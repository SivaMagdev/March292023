<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Cart Reservation v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\CartReservation\Observer;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\CatalogInventory\Helper\Data as InventoryHelper;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\Observer;
use Plumrocket\CartReservation\Helper\Data;
use Plumrocket\CartReservation\Helper\Item;

class QuoteItemQtySetAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Item
     */
    private $itemHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Product
     */
    private $productHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param Data                                        $dataHelper
     * @param \Plumrocket\CartReservation\Helper\Config   $configHelper
     * @param Item                                        $itemHelper
     * @param \Plumrocket\CartReservation\Helper\Product  $productHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\RequestInterface     $request
     */
    public function __construct(
        Data $dataHelper,
        \Plumrocket\CartReservation\Helper\Config $configHelper,
        Item $itemHelper,
        \Plumrocket\CartReservation\Helper\Product $productHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->itemHelper = $itemHelper;
        $this->productHelper = $productHelper;
        $this->dateTime = $dateTime;
        $this->request = $request;
    }

    /**
     * Worked for different kind of change qty, e.g.:
     * - add to cart
     * - update on cart
     * - update on mini cart
     * - update on product page
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (! $this->dataHelper->moduleEnabled()) {
            return;
        }

        $quoteItem = $observer->getItem();
        $productId = $quoteItem->getProductId();

        // Stop if exist other error of qty validation.
        if ($quoteItem->getHasError()) {
            return;
        }

        // Stop if reservation is disabled for product.
        if (false === $this->productHelper->reservationEnabled($productId)) {
            return;
        }

        // If item is expired and product is reserved then add message and block the checkout.
        if ($this->itemHelper->getReservationStatus($quoteItem) == Item::RESERVATION_GUEST_DISABLED) {
            if (! $this->request->isPost() && ! $this->isQtyAllowed($quoteItem, false)) {
                $this->addErrorInfoToQuote(
                    $quoteItem,
                    __('Item "%1" is reserved.', $quoteItem->getName())
                );
            }

            // Stop because reservation is disabled for guests.
            return;
        } else {
            if (! $this->request->isPost() && ! $this->isQtyAllowed($quoteItem, false)) {
                $items = $this->productHelper->getReservations($quoteItem->getProductId());
                $timer = $items[$quoteItem->getProductId()];

                // If reserved qty is more than max allowed qty
                //(if admin added or mistake is in calculations) then block last added items.
                if ($timer['max_qty'] > 0
                    && $timer['reserved_qty'] > $timer['max_qty']
                    && $timer['timer_expire_at'] == $quoteItem->getData('timer_expire_at')
                ) {
                    $allowedQty = $this->getItemQty($quoteItem) - ($timer['reserved_qty'] - $timer['max_qty']);
                }

                // If item is expired (other customer reserved it item).
                if ($this->dateTime->gmtTimestamp() >= $quoteItem->getData('timer_expire_at')) {
                    $allowedQty = $timer['max_qty'] - $timer['reserved_qty'];
                }

                if (isset($allowedQty)) {
                    if ($allowedQty >= 1) {
                        $message = __('Item "%1" is reserved. Allowed qty: %2', $quoteItem->getName(), $allowedQty);
                    } else {
                        $message = __('Item "%1" is reserved.', $quoteItem->getName());
                    }

                    $this->addErrorInfoToQuote($quoteItem, $message);
                }
                return;
            }
        }

        if ($quoteItem->getId()) {
            /**
             * Update item qty.
             */
            if ($this->getRequestedQty($quoteItem) > $this->getItemQty($quoteItem)) {
                $hasError = false;
                // Qty control always use child simple products.
                // But use parent qty as requested qty for configurable and bundle products on update requests.
                if (in_array($quoteItem->getProductType(), [Configurable::TYPE_CODE, Bundle::TYPE_CODE])) {
                    $children = $quoteItem->getChildren();
                    foreach ($children as $childItem) {
                        if (! $this->isQtyAllowed($childItem)) {
                            $hasError = true;
                            break;
                        }
                    }
                } elseif (! $this->isQtyAllowed($quoteItem)) {
                    $hasError = true;
                }

                if ($hasError) {
                    $quoteItem->addErrorInfo(
                        Data::SECTION_ID,
                        InventoryHelper::ERROR_QTY_INCREMENTS,
                        __('Item "%1" is reserved.', $quoteItem->getName())
                    );
                }
            }
        } else {
            /**
             * Add to cart or Update on product page.
             */
            // Don't check parent items.
            if (in_array($quoteItem->getProductType(), [Configurable::TYPE_CODE, Bundle::TYPE_CODE])) {
                return;
            }

            if ($timers = $this->productHelper->getTimers($productId, $quoteItem->getQuoteId())) {
                $quoteItem->addErrorInfo(
                    Data::SECTION_ID,
                    InventoryHelper::ERROR_QTY,
                    __('Item "%1" is reserved.', $quoteItem->getName())
                );
            } elseif (! $this->isQtyAllowed($quoteItem)) {
                $quoteItem->addErrorInfo(
                    Data::SECTION_ID,
                    InventoryHelper::ERROR_QTY,
                    __('Item "%1" is reserved.', $quoteItem->getName())
                );
            }
        }
    }

    /**
     * Check if qty that customer wants is allowed
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param boolean $skipCurrentQuote
     * @return boolean
     */
    private function isQtyAllowed($quoteItem, $skipCurrentQuote = true)
    {
        $productIds = [$quoteItem->getProductId()];
        foreach ($quoteItem->getChildren() as $child) {
            $productIds[] = $child->getProductId();
        }

        $items = $this->productHelper->getReservations(
            $productIds,
            $skipCurrentQuote ? $quoteItem->getQuoteId() : null
        );

        if ($quoteItem->getHasChildren()) {
            foreach ($quoteItem->getChildren() as $child) {
                $productId = $child->getProductId();
                if (isset($items[$productId])
                    && $items[$productId]['reserved_qty']
                    + $this->getRequestedQty($quoteItem) > $items[$productId]['max_qty']
                ) {
                    return false;
                }
            }
        } else {
            $productId = $quoteItem->getProductId();
            if (isset($items[$productId])
                && $items[$productId]['reserved_qty']
                + $this->getRequestedQty($quoteItem) > $items[$productId]['max_qty']
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get requested qty of quote item
     *
     * @param  \Magento\Quote\Model\Quote\Item $quoteItem
     * @return int|float
     */
    private function getRequestedQty($quoteItem)
    {
        if ($quoteItem->getId()) {
            $qty = $quoteItem->getQty();
            if ($parentItem = $quoteItem->getParentItem()) {
                $qty *= $parentItem->getQty();
            }
        } else {
            // If item is new, requested qty is equal item qty.
            $qty = $this->getItemQty($quoteItem);
        }

        return $qty;
    }

    /**
     * Get qty of quote item
     *
     * @param  \Magento\Quote\Model\Quote\Item $quoteItem
     * @return int|float
     */
    private function getItemQty($quoteItem)
    {
        if ($quoteItem->getId()) {
            // Use origin data for existing items, because it keeps same data that in DB.
            // Otherwise it can keep the prepared to save qty (requested qty).
            $qty = $quoteItem->getOrigData('qty');
            if ($parentItem = $quoteItem->getParentItem()) {
                $qty *= $parentItem->getOrigData('qty');
            }
        } else {
            $qty = $quoteItem->getQty();
            if ($parentItem = $quoteItem->getParentItem()) {
                $qty *= $parentItem->getQty();
            }
        }

        return $qty;
    }

    /**
     * Add error information to Quote Item
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param string $message
     * @param string $quoteMessage
     * @return void
     */
    private function addErrorInfoToQuote($quoteItem, $message, $quoteMessage = null)
    {
        if ($quoteItem->getParentItemId()) {
            // Remove previous reservation messages (for items with children)
            $quoteItem->getParentItem()->removeErrorInfosByParams(Data::SECTION_ID);
            $quoteItem->getParentItem()->addErrorInfo(
                Data::SECTION_ID,
                InventoryHelper::ERROR_QTY,
                $message
            );
        } else {
            $quoteItem->addErrorInfo(
                Data::SECTION_ID,
                InventoryHelper::ERROR_QTY,
                $message
            );
        }
        $quoteItem->getQuote()->addErrorInfo(
            'error',
            Data::SECTION_ID,
            InventoryHelper::ERROR_QTY,
            $quoteMessage
        );
    }
}

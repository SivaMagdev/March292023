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

use Magento\Framework\Event\Observer;
use Plumrocket\CartReservation\Helper\Data;
use Plumrocket\CartReservation\Helper\Item;
use Plumrocket\CartReservation\Model\Config\Source\TimerType;

class CustomerLogin implements \Magento\Framework\Event\ObserverInterface
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
     * @var Item
     */
    private $itemHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Product
     */
    private $productHelper;

    /**
     * @param Data                                       $dataHelper
     * @param \Plumrocket\CartReservation\Helper\Config  $configHelper
     * @param Item                                       $itemHelper
     * @param \Plumrocket\CartReservation\Helper\Product $productHelper
     */
    public function __construct(
        Data $dataHelper,
        \Plumrocket\CartReservation\Helper\Config $configHelper,
        Item $itemHelper,
        \Plumrocket\CartReservation\Helper\Product $productHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->itemHelper = $itemHelper;
        $this->productHelper = $productHelper;
    }

    /**
     * Quote has been merged on this step.
     * The customer quote is primary and the quest quote loses its data
     * of time after merge if both quotes contain same products.
     *
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (! $this->dataHelper->moduleEnabled()) {
            return;
        }

        /**
         * Entrance for guest:
         * - If time mode 2 then set to item new cart-time
         * - Guest hasn't cart/checkout timer mode so always set cart-time.
         * - If guest was on checkout and returns to it then need correct to calculate him time
         *  . need to set current mode to "cart"
         * - Update time only if item is not reserved yet
         * - Remove item if it is expired and reserved (skip, currently it works via ajax)
         * - Global timer don't need to update generally because time sets as global for every item
         *
         * Merge of items:
         * - If timer is not global, time of items leave as is
         * - Expired items need to remove if config has it
         * - If timer is global then update it
         */

        $this->itemHelper->switchMode(Data::TIMER_MODE_CART);

        $items = $this->itemHelper->getQuoteItems();
        foreach ($items as $item) {
            if ($this->configHelper->getCartReservationType() == TimerType::TYPE_SEPARATE) {
                // Item can change id after merge so need to update saved timer code.
                $item->removeOption('additional_options')->saveItemOptions();
            }

            if ($this->itemHelper->getReservationStatus($item) == Item::RESERVATION_GUEST_DISABLED) {
                if ($item->getData('prcr_guest_time_updated')) {
                    continue;
                }

                $productId = $item->getProductId();
                $items = $this->productHelper->getReservations($productId, $item->getQuoteId());

                if (isset($items[$productId])
                    && $items[$productId]['reserved_qty'] + $item->getQty() > $items[$productId]['max_qty']
                ) {
                    continue;
                }

                $expireAt = $this->dataHelper->getExpireAt(
                    $this->configHelper->getCartTime()
                );

                $this->itemHelper->updateItem($item, [
                    'timer_expire_at' => $expireAt,
                    'original_cart_expire_at' => $expireAt,
                    'prcr_guest_time_updated' => true
                ]);
            }
        }

        if ($this->configHelper->getCartReservationType() == TimerType::TYPE_GLOBAL) {
            $this->itemHelper->updateGlobalTimer(
                $this->itemHelper->getQuoteId()
            );
        }
    }
}

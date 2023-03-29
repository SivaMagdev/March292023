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
use Plumrocket\CartReservation\Model\Config\Source\TimerMode;
use Plumrocket\CartReservation\Model\Config\Source\TimerType;

class QuoteAddItem implements \Magento\Framework\Event\ObserverInterface
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
     * @var \Plumrocket\CartReservation\Helper\Product
     */
    private $productHelper;

    /**
     * @var Item
     */
    private $itemHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @param Data                                        $dataHelper
     * @param \Plumrocket\CartReservation\Helper\Config   $configHelper
     * @param Item                                        $itemHelper
     * @param \Plumrocket\CartReservation\Helper\Product  $productHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        Data $dataHelper,
        \Plumrocket\CartReservation\Helper\Config $configHelper,
        Item $itemHelper,
        \Plumrocket\CartReservation\Helper\Product $productHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->dataHelper = $dataHelper;
        $this->itemHelper = $itemHelper;
        $this->configHelper = $configHelper;
        $this->productHelper = $productHelper;
        $this->dateTime = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (! $this->dataHelper->moduleEnabled()) {
            return;
        }

        $quoteItem = $observer->getQuoteItem();

        // Set time when adding to cart, but stop for update of item qty.
        if (! $quoteItem->isObjectNew()) {
            return;
        }

        // Remember product ids
        $productIds = $this->dataHelper->getProductIds();
        $productIds[] = $quoteItem->getProductId();
        $this->dataHelper->setProductIds($productIds);

        switch (true) {
            /**
             * Disable reservation for product
             * (check it first, because if reservation disabled then guest mode doesn't matter)
             */
            case false === $this->productHelper->reservationEnabled($quoteItem->getProductId())
                && ($expireAt = Item::RESERVATION_DISABLED):
                // no break
            /**
             * Disable reservation for virtual products.
             */
            case $this->productHelper->isVirtual($quoteItem->getProduct())
                && ! $this->configHelper->isEnabledReservationForVirtual()
                && ($expireAt = Item::RESERVATION_DISABLED):
                // no break

            /**
             * Disable reservation for guests.
             */
            case $this->dataHelper->isGuestMode()
                && ($expireAt = Item::RESERVATION_GUEST_DISABLED):
            $quoteItem->setData('timer_expire_at', $expireAt);
            break;

            /**
             * Item has time after merge to customer quote.
             */
            case $quoteItem->hasData('timer_expire_at'):
                break;

            /**
             * Default reservation logic.
             */
            default:
                $expireAt = $this->dataHelper->getExpireAt(
                    $this->configHelper->getCartTime()
                );

                // Add timer data. Don't save, it will be later.
                $quoteItem
                    ->setData('timer_expire_at', $expireAt)
                    ->setData('original_cart_expire_at', $expireAt);

                // If customer clicks the add to cart button, but last page is checkout then get checkout config time.
                if ($this->configHelper->getTimerMode() == TimerMode::SEPARATE
                    && $this->dataHelper->getTimerMode() == Data::TIMER_MODE_CHECKOUT
                ) {
                    $expireAt = $this->dataHelper->getExpireAt(
                        $this->configHelper->getCheckoutTime()
                    );

                    $quoteItem
                        ->setData('timer_expire_at', $expireAt)
                        ->setData('original_checkout_expire_at', $expireAt)
                        ->setData('cart_time', $expireAt - $this->dateTime->gmtTimestamp());
                }

                // If time is global for all items then update them.
                // Quote is missing for first item, so need to update global time starting with second item.
                if ($quoteItem->getQuoteId()
                    && ($this->configHelper->getCartReservationType() == TimerType::TYPE_GLOBAL
                        || $this->dataHelper->getTimerMode() == Data::TIMER_MODE_CHECKOUT)
                ) {
                    $this->itemHelper->updateGlobalTimer($quoteItem->getQuoteId(), 0, null, $expireAt);
                }
                break;
        }
    }
}

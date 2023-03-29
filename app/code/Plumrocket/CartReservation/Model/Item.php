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
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\CartReservation\Model;

use Magento\Checkout\Model\Cart;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Plumrocket\CartReservation\Api\ItemInterface;
use Plumrocket\CartReservation\Helper\Config;
use Plumrocket\CartReservation\Helper\Data;
use Plumrocket\CartReservation\Helper\Item as ItemHelper;
use Plumrocket\CartReservation\Model\Config\Source\EndAction;

/**
 * @since 2.3.0
 */
class Item implements ItemInterface
{
    /**
     * @var \Plumrocket\CartReservation\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Item
     */
    protected $itemHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @param \Plumrocket\CartReservation\Helper\Data $dataHelper
     * @param \Plumrocket\CartReservation\Helper\Config $configHelper
     * @param \Plumrocket\CartReservation\Helper\Item $itemHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        Data $dataHelper,
        Config $configHelper,
        ItemHelper $itemHelper,
        DateTime $dateTime,
        Cart $cart
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->itemHelper = $itemHelper;
        $this->dateTime = $dateTime;
        $this->cart = $cart;
    }

    /**
     * @inheritDoc
     */
    public function remove(): array
    {
        $data = ['success' => false];

        if (! $this->dataHelper->moduleEnabled()) {
            return $data;
        }

        if ($this->configHelper->getCartEndAction() != EndAction::REMOVE_ITEM) {
            return $data;
        }

        $hasRemoved = false;
        $items = $this->itemHelper->getQuoteItems();
        foreach ($items as $item) {
            // Stop if item is not expired.
            if ($item->getData('timer_expire_at') > $this->dateTime->gmtTimestamp()
                || $this->itemHelper->getReservationStatus($item) == ItemHelper::RESERVATION_DISABLED
            ) {
                continue;
            }

            // Don't remove items in guest mode.
            if ($this->dataHelper->isGuestMode()
                && $this->itemHelper->getReservationStatus($item) == ItemHelper::RESERVATION_GUEST_DISABLED
            ) {
                continue;
            }

            // Stop if item is already deleted or is not visible.
            if ($item->isDeleted() || $item->getParentItemId()) {
                continue;
            }

            $this->cart->removeItem($item->getId());
            $messages[] = __('Item "%1" was removed from your cart by expiration of reservation.', $item->getName());
            $hasRemoved = true;
        }

        if ($hasRemoved) {
            $this->cart->save();
            $data = [
                'success' => true,
                'messages' => $messages
            ];
        }

        return $data;
    }
}

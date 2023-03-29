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
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\CartReservation\Observer;

use Magento\Framework\Event\Observer;

/**
 * Class SalesOrderPlaceAfter
 *
 * @package Plumrocket\CartReservation\Observer
 */
class SalesOrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Plumrocket\CartReservation\Helper\Item
     */
    private $itemHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @param \Plumrocket\CartReservation\Helper\Item     $itemHelper
     * @param \Plumrocket\CartReservation\Helper\Data     $dataHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Plumrocket\CartReservation\Helper\Item $itemHelper,
        \Plumrocket\CartReservation\Helper\Data $dataHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->itemHelper = $itemHelper;
        $this->dataHelper = $dataHelper;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (! $this->dataHelper->moduleEnabled()) {
            return;
        }

        $order = $observer->getOrder();
        $items = $this->itemHelper->getQuoteItems($order->getQuoteId());
        $timestamp = $this->dateTime->gmtTimestamp();

        foreach ($items as $item) {
            $this->itemHelper->setReservationStatus($item, $timestamp);
            $item->save();
        }
    }
}

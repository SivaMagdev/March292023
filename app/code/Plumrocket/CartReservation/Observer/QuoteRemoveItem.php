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
use Plumrocket\CartReservation\Model\Config\Source\TimerType;

class QuoteRemoveItem implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Plumrocket\CartReservation\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Item
     */
    private $itemigHelper;

    /**
     * @param \Plumrocket\CartReservation\Helper\Data   $dataHelper
     * @param \Plumrocket\CartReservation\Helper\Config $configHelper
     * @param \Plumrocket\CartReservation\Helper\Item   $itemHelper
     */
    public function __construct(
        \Plumrocket\CartReservation\Helper\Data $dataHelper,
        \Plumrocket\CartReservation\Helper\Config $configHelper,
        \Plumrocket\CartReservation\Helper\Item $itemHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->itemHelper = $itemHelper;
    }

    /**
     * This method uses only for parent item.
     * Child item will removed automaticaly by foreighn key.
     *
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (! $this->dataHelper->moduleEnabled()) {
            return;
        }

        $quoteItem = $observer->getQuoteItem();

        // If time is global for all items then update them.
        if ($this->configHelper->getCartReservationType() == TimerType::TYPE_GLOBAL
            && $quoteItem->getQuoteId()
            && $quoteItem->getQuote()->getItemsCount() > 1
        ) {
            $this->itemHelper->updateGlobalTimer($quoteItem->getQuoteId(), $quoteItem->getId());
        }
    }
}

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
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\CartReservation\Cron;

use Plumrocket\CartReservation\Model\Config\Source\EndAction;

class QuoteRemoveItem
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
    private $itemHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    private $cartFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @param \Plumrocket\CartReservation\Helper\Data                    $dataHelper
     * @param \Plumrocket\CartReservation\Helper\Config                  $configHelper
     * @param \Plumrocket\CartReservation\Helper\Item                    $itemHelper
     * @param \Magento\Framework\App\ResourceConnection                  $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                $dateTime
     * @param \Magento\Checkout\Model\CartFactory                        $cartFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
     */
    public function __construct(
        \Plumrocket\CartReservation\Helper\Data $dataHelper,
        \Plumrocket\CartReservation\Helper\Config $configHelper,
        \Plumrocket\CartReservation\Helper\Item $itemHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->itemHelper = $itemHelper;
        $this->resource = $resource;
        $this->dateTime = $dateTime;
        $this->cartFactory = $cartFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
    }

    /**
     * Remove quote items
     *
     * @return void
     */
    public function execute()
    {
        if (! $this->dataHelper->moduleEnabled()) {
            return;
        }

        if ($this->configHelper->getCartEndAction() != EndAction::REMOVE_ITEM) {
            return;
        }

        if (! $quoteIds = $this->getExpiredQuotes()) {
            return;
        }

        $quotes = $this->quoteCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => $quoteIds]);

        foreach ($quotes as $quote) {
            $hasRemoved = false;
            $items = $quote->getAllVisibleItems();
            foreach ($items as $item) {
                // Stop if item is not expired.
                if ($item->getData('timer_expire_at') > $this->dateTime->gmtTimestamp()
                    || $this->itemHelper->getReservationStatus($item)
                ) {
                    continue;
                }

                $quote->removeItem($item->getId());
                $hasRemoved = true;
            }

            if ($hasRemoved) {
                $quote->getBillingAddress();
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->collectTotals();
                $quote->save();
            }
        }
    }

    /**
     * Retrieve list of quotes with expired items
     *
     * @return int[]
     */
    private function getExpiredQuotes()
    {
        $connection = $this->resource->getConnection();
        $query = $connection->select()
            ->from(
                [
                    'q' => $this->resource->getTableName('quote_item')
                ],
                [
                    'DISTINCT(q.quote_id)'
                ]
            )
            ->joinLeft(
                [
                    'o' => $this->resource->getTableName('sales_order_item')
                ],
                'o.quote_item_id = q.item_id',
                []
            )
            ->where('q.timer_expire_at <= ?', $this->dateTime->gmtTimestamp())
            ->where('o.item_id IS NULL');

        return $connection->fetchCol($query);
    }
}

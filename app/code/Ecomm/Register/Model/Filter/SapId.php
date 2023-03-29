<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecomm\Register\Model\Filter;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\ResourceModel\Order\Collection;


class SapId implements \Magento\OrderHistorySearch\Model\Filter\FilterInterface
{
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * InvoiceNumber constructor.
     *
     * @param InvoiceRepository $invoiceRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        InvoiceRepository $invoiceRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * @inheritdoc
     */
    public function applyFilter(Collection $ordersCollection, $value): Collection
    {

        //$ordersCollection->addFieldToFilter(OrderInterface::ENTITY_ID, ['in' => $orderIds]);
        /*$ordersCollection->getSelect()->join(
            array('payment' => $ordersCollection->getResource()->getTable('sales_order_payment')),
            'payment.parent_id = main_table.entity_id',
            array()
        );
        $ordersCollection->addFieldToFilter('method','purchaseorder');*/
        $ordersCollection->addFieldToFilter('sap_id',array('like' => '%'.$value.'%'));

        return $ordersCollection;
    }
}

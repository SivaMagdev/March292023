<?php

namespace Ecomm\Api\Plugin;

use Magento\Sales\Api\Data\InvoiceExtensionFactory;
use Magento\Sales\Api\Data\InvoiceExtensionInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceSearchResultInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;

/**
 * Class InvoiceRepositoryPlugin
 */
class InvoiceRepositoryPlugin
{
    /**
     * Invoice Extension Attributes Factory
     *
     * @var InvoiceExtensionFactory
     */
    protected $extensionFactory;

    protected $_resourceConnection;

    protected $_orderRepository;


    public function __construct(
        InvoiceExtensionFactory $extensionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Model\OrderRepository $orderRepository
    )
    {
        $this->extensionFactory = $extensionFactory;
        $this->_resourceConnection      = $resourceConnection;
        $this->_orderRepository         = $orderRepository;
    }

     /**
     *
     * @param InvoiceRepositoryInterface $subject
     * @param InvoiceInterface $invoice
     *
     * @return InvoiceInterface
     */
    public function afterGet(InvoiceRepositoryInterface $subject, InvoiceInterface $invoice)
    {
        $extensionAttributes = $invoice->getExtensionAttributes();

        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();

        if ($extensionAttributes) {

            $connection = $this->_resourceConnection->getConnection();

            $select = $connection->select()
            ->from(['si' => 'ecomm_sap_order_invoice'], ['*'])
            ->where("si.m_invoice_id = :m_invoice_id");
            $bind = ['m_invoice_id'=>$invoice->getId()];
            $data = $connection->fetchRow($select, $bind);
            if(isset($data['invoice_id'])){
                $extensionAttributes->setInvoiceIdocNumber((int)$data['invoice_id']);
            } else {
                $extensionAttributes->setInvoiceIdocNumber('');
            }

            $_order = $this->_orderRepository->get($invoice->getOrderId());

            foreach ($_order->getAllVisibleItems() as $_item) {
                $priceType = $_item->getPriceType();
            }
            if ($priceType) {
                $extensionAttributes->setPriceType($priceType);
            } else {
                $extensionAttributes->setPriceType('');
            }
            $extensionAttributes->setSapId($_order->getSapId());
            $extensionAttributes->setOrderIncrementId($_order->getIncrementId());

            $invoice->setExtensionAttributes($extensionAttributes);
        }

        return $invoice;
    }

    public function afterGetList(
        \Magento\Sales\Api\InvoiceRepositoryInterface $subject,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $resultInvoice
    ) {
        foreach ($resultInvoice->getItems() as $invoice) {
            $this->afterGet($subject, $invoice);
        }

        return $resultInvoice;
    }
}
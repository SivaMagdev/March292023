<?php
namespace Ecomm\OrderExport\Block;

class Export extends \Magento\Framework\View\Element\Template
{
 protected $_orderCollectionFactory;
protected $customer;
public function __construct(\Magento\Customer\Model\Session $customer,
 \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory)
{
    $this->customer = $customer;
    $this->_orderCollectionFactory = $orderCollectionFactory;
}
public function Orderexport()
{
    $customer = $this->customer;
    $customerName = $customer->getName();
    $customerId = $customer->getId();
    if (!$this->order)
        {
            $this->order = $this
                ->_orderCollectionFactory
                ->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('customer_id', $customerId)->setOrder('created_at', 'desc');
        }
}
}
?>
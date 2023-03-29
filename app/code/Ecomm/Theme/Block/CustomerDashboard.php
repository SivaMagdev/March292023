<?php
namespace Ecomm\Theme\Block;


class CustomerDashboard extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
	protected $_orderCollectionFactory;
	
	protected $customerSession;

	protected $_orderFactory;

	protected $invoices;

	protected $_date;

	protected $invoiceCollectionFactory;
        
	public function __construct(
		\Magento\Backend\Block\Template\Context $context, 
		\Magento\Customer\Model\Session $customerSession,       
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Sales\Model\OrderFactory $orderFactory,
		array $data = []
	)
	{
		$this->_orderCollectionFactory = $orderCollectionFactory;
		$this->customerSession      = $customerSession;
		$this->_orderFactory   = $orderFactory;
		$this->invoiceCollectionFactory = $invoiceCollectionFactory;
		$this->_date 				    = $date;
		parent::__construct($context, $data);
	}
	public function getOrderCollection() 
	{
		$customerId = $this->customerSession->getCustomer()->getId();
		$collection = $this->_orderCollectionFactory->create()
				->addAttributeToSelect('*')
				->addFieldToFilter('customer_id', $customerId)
	            ->setOrder('created_at','desc')
	            ->setPageSize(5);
		return $collection;
	}
	public function getLoggedinCustomerName(){
		$customerId = $this->customerSession->getCustomer()->getName();
		return $customerId;
	}
	public function getOrderStatus()
	{
		$today_date = $this->_date->date('Y-m-d');
		$status = ['out_for_delivery', 'complete'];
		$customerId = $this->customerSession->getCustomer()->getId();
		$orderstatus = $this->_orderFactory->create()->getCollection()
		->addFieldToSelect('*')
		->addFieldToFilter('customer_id',$customerId)
		->addFieldToFilter('status', ['in' => $status])
		->addFieldToFilter('rgdd_delivery_date', array('gteq' => $today_date))
		->setOrder('created_at','desc');
		//echo $orderstatus->getSelect();
		return $orderstatus;
	}
	public function getInvoiceList()
    {
       $customerId = $this->customerSession->getCustomer()->getId();
       $orders =  $this->_orderCollectionFactory->create()
       ->addfieldToFilter('customer_id', $customerId)
	   ->addFieldToSelect( '*');
       $orderids = $orders->getColumnValues('entity_id');
    //    echo '<pre>';
    //    print_r($orderids);
    //    exit;
       if(count($orderids))
       {
           $this->invoices = $this->invoiceCollectionFactory->create()
               ->addFieldToSelect('*')->addFieldToFilter('order_id',['in' => $orderids])->setOrder('created_at','desc')->setPageSize(5);
       }
       return $this->invoices;
	}
	public function getPrintInvoiceUrl($invoice)
    {
        return $this->getUrl('sales/order/printInvoice', ['invoice_id' => $invoice->getId()]);
    }
}
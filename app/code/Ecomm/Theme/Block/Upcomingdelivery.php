<?php
namespace Ecomm\Theme\Block;

use Magento\Store\Model\StoreManagerInterface;


class Upcomingdelivery extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    protected $customerSession;

    protected $customerRepository;

    protected $_orderFactory;

    protected $_date;

    protected $_helper;

    public function __construct(
        StoreManagerInterface $storeManager,
    	\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magestore\Bannerslider\Api\BannerListInterface $bannerListInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Ecomm\Theme\Helper\Data $helper,
        array $data = []
    ) {

        $this->storeManager = $storeManager;
        $this->customerSession      = $customerSession;
        $this->customerRepository   = $customerRepository;
        $this->_orderFactory   = $orderFactory;
        $this->bannerListInterface   = $bannerListInterface;
        $this->_helper   = $helper;
        $this->httpContext = $httpContext;
        $this->_date 				    = $date;
        $this->_isScopePrivate = true;

        parent::__construct($context, $data);
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
        ->setOrder('created_at','desc')
        ->setPageSize(1);
        return $orderstatus;
    }

    public function getLoggedinCustomerName(){
		$customerId = $this->customerSession->getCustomer()->getName();
		return $customerId;
	}

}
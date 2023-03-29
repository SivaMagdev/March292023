<?php
namespace Ecomm\Theme\Block;

use Magento\Store\Model\StoreManagerInterface;


class Recentorders extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    
    protected $_orderCollectionFactory;
    
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
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magestore\Bannerslider\Api\BannerListInterface $bannerListInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Ecomm\Theme\Helper\Data $helper,
        array $data = []
    ) {

        $this->storeManager = $storeManager;
        $this->customerSession      = $customerSession;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->customerRepository   = $customerRepository;
        $this->_orderFactory   = $orderFactory;
        $this->bannerListInterface   = $bannerListInterface;
        $this->_helper   = $helper;
        $this->httpContext = $httpContext;
        $this->_date 				    = $date;
        $this->_isScopePrivate = true;

        parent::__construct($context, $data);
	}

    public function getOrderCollection() 
	{
        $customerId = $this->customerSession->getCustomer()->getId();
		$collection = $this->_orderCollectionFactory->create()
				->addAttributeToSelect('*')
				->addFieldToFilter('customer_id', $customerId)
	            ->setOrder('created_at','desc')
	            ->setPageSize(2);
		return $collection;
	}

    public function getLoggedinCustomerName(){
		$customerId = $this->customerSession->getCustomer()->getName();
		return $customerId;
	}

}
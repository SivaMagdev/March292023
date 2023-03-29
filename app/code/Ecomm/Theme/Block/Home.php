<?php
namespace Ecomm\Theme\Block;

use Magento\Store\Model\StoreManagerInterface;


class Home extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    protected $customerSession;

    protected $customerRepository;

    protected $_orderFactory;

    protected $_helper;

    public function __construct(
        StoreManagerInterface $storeManager,
    	\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magestore\Bannerslider\Api\BannerListInterface $bannerListInterface,
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

        parent::__construct($context, $data);
	}

    public function getOrderCount()
    {

        $status = ['new', 'processing'];
        $customerId = $this->httpContext->getValue('customer_id');
        $this->orders = $this->_orderFactory->create()->getCollection()
        ->addFieldToSelect('*')
        ->addFieldToFilter('customer_id',$customerId)
        ->addFieldToFilter('state', ['in' => $status])
        ->setOrder('created_at','desc');
        return count($this->orders);
    }

    public function getDeliveredOrderCount()
    {

        $status = ['complete'];
        $customerId = $this->httpContext->getValue('customer_id');
        $this->orders = $this->_orderFactory->create()->getCollection()
        ->addFieldToSelect('*')
        ->addFieldToFilter('customer_id',$customerId)
        ->addFieldToFilter('state', ['in' => $status])
        ->setOrder('created_at','desc');
        return count($this->orders);
    }

    public function getIsLoggedIn(){

        //echo 'isLoggedIn:- '.$this->customerSession->isLoggedIn();
        /*if ($this->customerSession->isLoggedIn()) {
            return true;
        } else {
            return false;
        }*/
        return $this->_helper->isLoggedIn();
    }

    public function getStepNumber(){

        $step_number = 0;
        if($this->httpContext->getValue('customer_id')) {
            $customerData= $this->customerRepository->getById($this->httpContext->getValue('customer_id'));

            if($customerData->getCustomAttribute('steps_status')){
                $step_number = $customerData->getCustomAttribute('steps_status')->getValue();
            }
        }

        //echo $step_number.'sdasdasd';

        return $step_number;
    }

    public function getIsApproved(){

        if($this->httpContext->getValue('customer_id')) {
            $customerData= $this->customerRepository->getById($this->httpContext->getValue('customer_id'));
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $_eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

            $attribute = $_eavConfig->getAttribute('customer', 'application_status');
            $options = $attribute->getSource()->getAllOptions();
            $application_statuses = [];
            foreach ($options as $option) {
                if ($option['value'] > 0) {
                    $application_statuses[$option['value']] = $option['label'];
                }
            }
            $application_status = 0;
            $approved_id = array_search("Approved",$application_statuses);
            if($customerData->getCustomAttribute('application_status')){
                $application_status = $customerData->getCustomAttribute('application_status')->getValue();
            }
            if($approved_id == $application_status){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getIsIncomplete(){

        if($this->httpContext->getValue('customer_id')) {
            $customerData= $this->customerRepository->getById($this->httpContext->getValue('customer_id'));
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $_eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

            $attribute = $_eavConfig->getAttribute('customer', 'application_status');
            $options = $attribute->getSource()->getAllOptions();
            $application_statuses = [];
            foreach ($options as $option) {
                if ($option['value'] > 0) {
                    $application_statuses[$option['value']] = $option['label'];
                }
            }
            $application_status = 0;
            $approved_id = array_search("Incomplete Form",$application_statuses);
            if($customerData->getCustomAttribute('application_status')){
                $application_status = $customerData->getCustomAttribute('application_status')->getValue();
            }
            if($approved_id == $application_status){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getBannerList($sliderId){
        //echo '<br />getBannerList:'.$sliderId;
        return $this->bannerListInterface->bannerList($sliderId);
    }

    public function getMediaURL(){
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
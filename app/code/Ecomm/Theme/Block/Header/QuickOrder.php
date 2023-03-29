<?php

namespace Ecomm\Theme\Block\Header;

class QuickOrder extends \Magento\Framework\View\Element\Html\Link
{
	protected $_template = 'Ecomm_Theme::quickorder.phtml';

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    protected $customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Ecomm\BellNotification\Model\BellNotification $bellNotification,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->customerSession      = $customerSession;
        $this->httpContext = $httpContext;
        $this->bellNotification = $bellNotification;

        parent::__construct($context, $data);
    }

    public function getCustomerIsLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

	public function getHref()
	{
	    return $this->getUrl('quickorder');
	}

	public function getLabel()
	{
		return __('Quick Order');
	}

    public function getNotificationCounter()
    {
        $bellNotification = [];
        $bellNotification = $this->bellNotification->getCollection()
        ->addFieldToSelect('*')->addFieldToFilter('assigned_user_id', $this->httpContext->getValue('customer_id'))
        ->addFieldToFilter('status', 1)
        ->setOrder('created_at','desc')
        ->setPageSize(15)
        ->getData();
        return count($bellNotification);
    }

    public function getIsApproved(){

    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$customerSession = $objectManager->get('\Magento\Customer\Model\Session');

    	//echo $customerSession->getId();

        if($this->httpContext->getValue('customer_id')) {
        	$customerRepository = $objectManager->get('\Magento\Customer\Api\CustomerRepositoryInterface');
            $customerData= $customerRepository->getById($this->httpContext->getValue('customer_id'));

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
}
?>
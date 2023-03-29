<?php
namespace Ecomm\Theme\Block;


class CustomerOrder extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    protected $customerSession;

    protected $customerRepository;

    public function __construct(
    	\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {

        $this->customerSession      = $customerSession;
        $this->customerRepository   = $customerRepository;

        parent::__construct($context);
	}

    public function getIsLoggedIn(){

        if ($this->customerSession->isLoggedIn()) {
            return true;
        } else {
            return false;
        }
    }

    public function getStepNumber(){

        //echo $this->customerSession->getId().'asdasdas';

        $customerData= $this->customerRepository->getById($this->customerSession->getId());

        $step_number = 0;

        if($customerData->getCustomAttribute('steps_status')){
            $step_number = $customerData->getCustomAttribute('steps_status')->getValue();
        }

        return $step_number;
    }

    public function getIsApproved(){
        $customerData= $this->customerRepository->getById($this->customerSession->getId());
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
    }
}
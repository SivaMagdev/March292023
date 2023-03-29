<?php
namespace Ecomm\Register\Controller\Account;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Edit extends \Magento\Customer\Controller\AbstractAccount implements HttpGetActionInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AttributeRepositoryInterface $eavAttributeRepository
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        CustomerRepositoryInterface $customerRepository,
        AttributeRepositoryInterface $eavAttributeRepository,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerRepository = $customerRepository;
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context);
    }

    /**
     * Forgot customer account information page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $customerId = $this->session->getCustomerId();

        //echo $customerId;

        $customer = $this->customerRepository->getById($customerId);

        //echo $customer->getResource()->getAttribute('application_status')->getFrontend()->getValue($customer);

        $attributes = $this->eavAttributeRepository->get(\Magento\Customer\Model\Customer::ENTITY, 'application_status');
        //$options = $attributes->getSource()->getAllOptions(false);

        //echo '<pre>'.print_r($options, true).'</pre>';

        $application_status = $attributes->getSource()->getOptionText($customer->getCustomAttribute("application_status")->getValue());

        if($application_status == 'Incomplete Form'){
            $this->messageManager->addErrorMessage('Please complete your profile');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/update');
            return $resultRedirect;
        } else if($application_status == 'Pending Approval'){
            $this->messageManager->addErrorMessage('Your application is under processing');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('');
            return $resultRedirect;
        } else if($application_status == 'Rejected'){
            $this->messageManager->addErrorMessage('Your application is rejected.');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/update');
            return $resultRedirect;
        }

        $block = $resultPage->getLayout()->getBlock('customer_edit');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }

        $data = $this->session->getCustomerFormData(true);
        $customerId = $this->session->getCustomerId();
        $customerDataObject = $this->customerRepository->getById($customerId);
        if (!empty($data)) {
            $this->dataObjectHelper->populateWithArray(
                $customerDataObject,
                $data,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
        }
        $this->session->setCustomerData($customerDataObject);
        $this->session->setChangePassword($this->getRequest()->getParam('changepass') == 1);

        $resultPage->getConfig()->getTitle()->set(__('Account Information'));
        return $resultPage;
    }
}

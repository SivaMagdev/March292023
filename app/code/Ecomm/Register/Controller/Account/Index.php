<?php

namespace Ecomm\Register\Controller\Account;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Customer\Controller\AbstractAccount implements HttpGetActionInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Session
     */
    protected $session;/**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AttributeRepositoryInterface $eavAttributeRepository
     * @param Redirect $resultRedirect
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AttributeRepositoryInterface $eavAttributeRepository,
        Redirect $resultRedirectFactory,
        PageFactory $resultPageFactory
    ) {
        $this->session = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

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



        //echo $customer->getCustomAttribute("application_status")->getValue();

        //echo '<pre>'.print_r($customer->getFirstName(), true).'</pre>';

        return $this->resultPageFactory->create();
    }
}

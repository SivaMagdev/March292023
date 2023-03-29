<?php

namespace Ecomm\Servicerequest\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;

class Requesthistory extends Action
{
    protected $resultPageFactory;

    protected $session;

    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $session,
        RedirectFactory $resultRedirectFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $session;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        $customerId = $this->session->getCustomerId();

        //echo $customerId;
        if($customerId == 0) {
            $this->messageManager->addErrorMessage('Please login to view the service request.');
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('customer/account/login');
                return $resultRedirect;
        }

        return $this->resultPageFactory->create();
    }
}
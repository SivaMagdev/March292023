<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecomm\Theme\Controller\CustomerDashboard;

use Magento\Customer\Model\Session;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    protected $session;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Session $session,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $customerId = $this->session->getCustomerId();

        //echo $customerId;
        if($customerId == 0) {
            $this->messageManager->addErrorMessage('Please login to access the Dashboard.');
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('customer/account/login');
                return $resultRedirect;
        }
        return $this->resultPageFactory->create();
    }
}


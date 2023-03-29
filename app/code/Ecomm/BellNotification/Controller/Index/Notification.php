<?php

namespace Ecomm\BellNotification\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;

class Notification extends Action
{
    protected $resultPageFactory;

    protected $session;

    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $session,
        \Ecomm\BellNotification\Model\BellNotification $bellNotification,
        RedirectFactory $resultRedirectFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $session;
        $this->bellNotification = $bellNotification;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        $customerId = $this->session->getCustomerId();

        $bellNotification = [];
        $count = 0;
        if($customerId !== 0) {
            $bellNotification = $this->bellNotification->getCollection()
            ->addFieldToSelect('*')->addFieldToFilter('assigned_user_id', $customerId)
            ->addFieldToFilter('status', 1)
            ->setOrder('created_at','desc')
            ->setPageSize(15)
            ->getData();
            $count = count($bellNotification);
        }

        $resultdata = array('bellNotification' => $bellNotification, 'count' => $count );


        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($resultdata));
    }
}
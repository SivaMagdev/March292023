<?php

namespace Ecomm\BellNotification\Block;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;

class Index extends Template
{
	/**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    public function __construct(
    	Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Ecomm\BellNotification\Model\BellNotification $bellNotification,
        \Magento\Customer\Model\Session $customerSession,
    	array $data = []
    )
    {
        $this->httpContext = $httpContext;
        $this->bellNotification = $bellNotification;
        $this->customerSession      = $customerSession;
        parent::__construct($context, $data);
    }

    public function getNotificationCollection()
    {
        $bellNotification = [];
        $bellNotification = $this->bellNotification->getCollection()
        ->addFieldToSelect('*')->addFieldToFilter('assigned_user_id', $this->httpContext->getValue('customer_id'))
        ->addFieldToFilter('status', 1)
        ->setOrder('created_at','desc')
        ->setPageSize(15)
        ->getData();
        return $bellNotification;
    }
}
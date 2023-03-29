<?php

namespace Ecomm\Theme\Block;

class Notification extends \Magento\Framework\View\Element\Html\Link
{
	/**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    protected $_template = 'Ecomm_Theme::notification.phtml';

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
    	\Ecomm\BellNotification\Model\BellNotification $bellNotification,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->bellNotification = $bellNotification;
        $this->customerSessionFactory      = $customerSessionFactory->create();
        $this->customerSession      = $customerSession;
    	parent::__construct($context);
	}

	public function getCustomerId()
    {
        //return $this->customerSession->getCustomerId();
        return $this->httpContext->getValue('customer_id');
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
?>
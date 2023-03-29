<?php

namespace Ecomm\Servicerequest\Block;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Ecomm\Servicerequest\Model\Servicerequest\Source\RequestType;

class Form extends Template
{
	protected $_requestType;

    public function __construct(
    	Context $context,
    	RequestType $requestType,
    	array $data = []
    )
    {
    	$this->_requestType = $requestType;
        parent::__construct($context, $data);
    }

    public function getFormAction()
    {
        return $this->getUrl('servicerequest/index/savepost', ['_secure' => true]);
    }

    public function getRequestType()
    {
    	return $this->_requestType->toOptionArray();
    }
}
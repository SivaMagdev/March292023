<?php

namespace Ecomm\ExclusivePrice\Model\ResourceModel;

class ExclusivePrice extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('ecomm_exclusive_price', 'exclusive_price_id');
	}

}
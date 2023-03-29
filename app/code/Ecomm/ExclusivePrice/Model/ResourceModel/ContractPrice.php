<?php

namespace Ecomm\ExclusivePrice\Model\ResourceModel;

class ContractPrice extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('ecomm_contract_extn', 'entity_id');
	}

}
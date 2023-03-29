<?php
namespace Ecomm\PriceEngine\Model\ResourceModel;


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
		$this->_init('ecomm_gpo_contract_price', 'entity_id');
	}

}
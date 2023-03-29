<?php
namespace Ecomm\ExclusivePrice\Model;

class ContractPrice extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'ecomm_contract_extn';



	protected function _construct()
	{
		$this->_init('Ecomm\ExclusivePrice\Model\ResourceModel\ContractPrice');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}
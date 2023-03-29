<?php
namespace Ecomm\PriceEngine\Model;
class ContractPrice extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'ecomm_gpo_contract_price';

	protected $_cacheTag = 'ecomm_gpo_contract_price';

	protected $_eventPrefix = 'ecomm_gpo_contract_price';

	protected function _construct()
	{
		$this->_init('Ecomm\PriceEngine\Model\ResourceModel\ContractPrice');
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
<?php


namespace Ecomm\ExclusivePrice\Model;

class ExclusivePrice extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'ecomm_exclusive_price';

	protected $_cacheTag = 'ecomm_exclusive_price';

	protected $_eventPrefix = 'ecomm_exclusive_price';

	protected function _construct()
	{
		$this->_init('Ecomm\ExclusivePrice\Model\ResourceModel\ExclusivePrice');
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
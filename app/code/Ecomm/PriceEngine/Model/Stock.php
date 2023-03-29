<?php
namespace Ecomm\PriceEngine\Model;

class Stock extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'ecomm_stock';

	protected $_cacheTag = 'ecomm_stock';

	protected $_eventPrefix = 'ecomm_stock';

	protected function _construct()
	{
		$this->_init('Ecomm\PriceEngine\Model\ResourceModel\Stock');
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
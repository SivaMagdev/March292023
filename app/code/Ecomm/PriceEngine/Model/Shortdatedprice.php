<?php
namespace Ecomm\PriceEngine\Model;
class Shortdatedprice extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'ecomm_shortdated_price';

	protected $_cacheTag = 'ecomm_shortdated_price';

	protected $_eventPrefix = 'ecomm_shortdated_price';

	protected function _construct()
	{
		$this->_init('Ecomm\PriceEngine\Model\ResourceModel\Shortdatedprice');
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
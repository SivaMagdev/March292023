<?php
namespace Ecomm\OrderHin\Model;

class HinData extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'ecomm_sales_order_hin';

	protected $_cacheTag = 'ecomm_sales_order_hin';

	protected $_eventPrefix = 'ecomm_sales_order_hin';

	protected function _construct()
	{
		$this->_init('Ecomm\OrderHin\Model\ResourceModel\HinData');
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
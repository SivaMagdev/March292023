<?php
namespace Ecomm\BellNotification\Model;
class BellNotification extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'ecomm_bell_notification';

	protected $_cacheTag = 'ecomm_bell_notification';

	protected $_eventPrefix = 'ecomm_bell_notification';

	protected function _construct()
	{
		$this->_init('Ecomm\BellNotification\Model\ResourceModel\BellNotification');
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
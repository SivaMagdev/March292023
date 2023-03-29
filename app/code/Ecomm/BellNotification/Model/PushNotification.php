<?php
namespace Ecomm\BellNotification\Model;

use Ecomm\BellNotification\Api\PushNotification\PushNotificationInterface;

class PushNotification extends \Magento\Framework\Model\AbstractModel implements PushNotificationInterface
{
	const CACHE_TAG = 'ecomm_push_notification';

	protected $_cacheTag = 'ecomm_push_notification';

	protected $_eventPrefix = 'ecomm_push_notification';

	protected function _construct()
	{
		$this->_init('Ecomm\BellNotification\Model\ResourceModel\PushNotification');
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

	/**
     * Get CustomerId
     *
     * @return string
     */
    public function getCustomerId()
    {
    	return $this->getData(PushNotificationInterface::CUSTOMER_ID);
    }

    /**
     * Set CustomerId
     *
     * @param $customer_id
     * @return mixed
     */
    public function setCustomerId($customer_id)
    {
    	return $this->setData(PushNotificationInterface::CUSTOMER_ID, $customer_id);
    }

    /**
     * Get DeviceToken
     *
     * @return string
     */
    public function getDeviceToken()
    {
    	return $this->getData(PushNotificationInterface::DEVICE_TOKEN);
    }

    /**
     * Set DeviceToken
     *
     * @param $device_token
     * @return mixed
     */
    public function setDeviceToken($device_token)
    {
    	return $this->setData(PushNotificationInterface::DEVICE_TOKEN, $device_token);
    }

    /**
     * Get DeviceType
     *
     * @return string
     */
    public function getDeviceType()
    {
    	return $this->getData(PushNotificationInterface::DEVICE_TYPE);
    }

    /**
     * Set DeviceType
     *
     * @param $device_type
     * @return mixed
     */
    public function setDeviceType($device_type)
    {
    	return $this->setData(PushNotificationInterface::DEVICE_TYPE, $device_type);
    }

    /**
     * Get Status
     *
     * @return bool|int
     */
    public function getStatus()
    {
    	return $this->getData(PushNotificationInterface::STATUS);
    }

    /**
     * Set Status
     *
     * @param $status
     * @return DataInterface
     */
    public function setStatus($status)
    {
    	return $this->setData(PushNotificationInterface::STATUS, $status);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
    	return $this->getData(PushNotificationInterface::CREATED_AT);
    }

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setCreatedAt($createdAt)
    {
    	return $this->setData(PushNotificationInterface::CREATED_AT, $createdAt);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
    	return $this->getData(PushNotificationInterface::UPDATED_AT);
    }

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return DataInterface
     */
    public function setUpdatedAt($updatedAt)
    {
    	return $this->setData(PushNotificationInterface::UPDATED_AT, $updatedAt);
    }
}
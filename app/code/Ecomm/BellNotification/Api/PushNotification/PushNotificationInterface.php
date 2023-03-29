<?php

namespace Ecomm\BellNotification\Api\PushNotification;

interface PushNotificationInterface
{
   /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                    = 'id';
    const CUSTOMER_ID           = 'customer_id';
    const DEVICE_TOKEN          = 'device_token';
    const DEVICE_TYPE           = 'device_type';
    const STATUS                = 'status';
    const CREATED_AT            = 'created_at';
    const UPDATED_AT            = 'updated_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param $id
     * @return DataInterface
     */
    public function setId($id);

    /**
     * Get CustomerId
     *
     * @return string
     */
    public function getCustomerId();

    /**
     * Set CustomerId
     *
     * @param $customer_id
     * @return string
     */
    public function setCustomerId($customer_id);

    /**
     * Get DeviceToken
     *
     * @return string
     */
    public function getDeviceToken();

    /**
     * Set DeviceToken
     *
     * @param $device_token
     * @return mixed
     */
    public function setDeviceToken($device_token);

    /**
     * Get DeviceType
     *
     * @return string
     */
    public function getDeviceType();

    /**
     * Set DeviceType
     *
     * @param $device_type
     * @return mixed
     */
    public function setDeviceType($device_type);

    /**
     * Get Status
     *
     * @return bool|int
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param $status
     * @return DataInterface
     */
    public function setStatus($status);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return DataInterface
     */
    public function setUpdatedAt($updatedAt);
}

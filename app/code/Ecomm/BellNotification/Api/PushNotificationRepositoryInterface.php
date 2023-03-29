<?php
 
namespace Ecomm\BellNotification\Api;

use Ecomm\BellNotification\Api\PushNotification\PushNotificationInterface;

interface PushNotificationRepositoryInterface
{
	/**
     * @param PushNotificationInterface $data
     * @return Ecomm\BellNotification\Api\PushNotificationRepositoryInterface[]
     */
	public function save(PushNotificationInterface $data);
}
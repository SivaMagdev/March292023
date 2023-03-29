<?php

namespace Ecomm\BellNotification\Model\Api;

use Ecomm\BellNotification\Api\PushNotificationRepositoryInterface;
use Ecomm\BellNotification\Api\PushNotification\PushNotificationInterface;

class PushNotification implements PushNotificationRepositoryInterface{

    /**
     * Constructor
     * \Ecomm\BellNotification\Model\PushNotification $pushNotification
     * 
     */
    public function __construct(
        \Ecomm\BellNotification\Model\PushNotification $pushNotification,
        \Magento\Framework\App\Request\Http $http,
        \Ecomm\BellNotification\Model\ResourceModel\PushNotification $pushNotificationModel,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory
    ){  
        $this->pushNotification         = $pushNotification;
        $this->http                     = $http;
        $this->pushNotificationModel    = $pushNotificationModel;
        $this->tokenFactory             = $tokenFactory;
    }

    public function save(PushNotificationInterface $data)
    {
            $pushNotificationDetails = $this->pushNotification->load($data->getCustomerId(), 'customer_id');
            if(!$pushNotificationDetails->getId())
            {
                $pushNotificationDetails->setData($data->getData());
             }else{
                $pushNotificationDetails->setCustomerId($data->getCustomerId());
                $pushNotificationDetails->setDeviceToken($data->getDeviceToken());
                $pushNotificationDetails->setDeviceType($data->getDeviceType());
            }
            $pushNotificationDetails->save();
        try {
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the data: %1',
                $exception->getMessage()
            ));
        }
        return 'success';
    }
}
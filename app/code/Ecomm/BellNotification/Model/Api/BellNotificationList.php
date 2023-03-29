<?php

namespace Ecomm\BellNotification\Model\Api;

use Ecomm\BellNotification\Api\BellNotificationListInterface;


class BellNotificationList implements BellNotificationListInterface{

    /**
     * Constructor
     * \Ecomm\BellNotification\Model\BellNotification $bellNotification
     * 
     */
    public function __construct(
        \Ecomm\BellNotification\Model\BellNotification $bellNotification,
        \Magento\Framework\App\Request\Http $http,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory
    ){  
        $this->bellNotification = $bellNotification;
        $this->http             = $http;
        $this->tokenFactory     = $tokenFactory;
    }

    public function getList()
    {
        $authorizationHeader = $this->http->getHeader('Authorization');

        $tokenParts = explode('Bearer', $authorizationHeader);
        $tokenPayload = trim(array_pop($tokenParts));

        /** @var Token $token */
        $token = $this->tokenFactory->create();
        $token->loadByToken($tokenPayload);

        $customerId = $token->getCustomerId();


        $bellNotification = [];
        $bellNotification = $this->bellNotification->getCollection()
        ->addFieldToSelect('*')->addFieldToFilter('assigned_user_id', $customerId)
        ->addFieldToFilter('status', 1)
        ->setOrder('created_at','desc')
        ->setPageSize(20)
        ->getData();

        return $bellNotification;
    }
}
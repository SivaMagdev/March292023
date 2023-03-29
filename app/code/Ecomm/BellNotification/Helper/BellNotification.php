<?php

namespace Ecomm\BellNotification\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * This class contains manipulation functions
 */
class BellNotification extends AbstractHelper {

    public function __construct(
        Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\SessionFactory $customersession,
        \Ecomm\BellNotification\Model\BellNotification $bellNotification
    ){
        parent::__construct ( $context );
        $this->httpContext = $httpContext;
        $this->_bellNotification = $bellNotification;
        $this->_customersession = $customersession;
        $this->_customer = $customer;
    }

    public function getNotificationCount()
    {
        $bellNotification = [];
        $bellNotification = $this->_bellNotification->getCollection()
        ->addFieldToSelect('*')->addFieldToFilter('assigned_user_id', $this->httpContext->getValue('customer_id'))
        ->addFieldToFilter('status', 1)
        ->setOrder('created_at','desc')
        ->setPageSize(15)
        ->getData();
        return count($bellNotification);
    }

    public function pushToNotification($type_id,$assigned_user_id,$type,$comment)
    {

        //$customerId = $this->_customersession->getCustomerId();
        $customerId = $this->httpContext->getValue('customer_id');

        $notificationCollection = [];

        if($customerId){
            $customerInfo = $this->_customer->load($customerId);
            $notificationCollection['customer_id'] = $customerId;
            $notificationCollection['customer_type'] = $customerInfo->getGroupId();
            $notificationCollection['type_id'] = $type_id;
            $notificationCollection['type'] = $type;
            $notificationCollection['assigned_user_id'] = $assigned_user_id;
            $notificationCollection['comment'] = $comment;
            $notificationCollection['status'] = 1;
        } else {
            $notificationCollection['customer_id'] = 0;
            $notificationCollection['customer_type'] = 0;
            $notificationCollection['type_id'] = $type_id;
            $notificationCollection['type'] = $type;
            $notificationCollection['assigned_user_id'] = $assigned_user_id;
            $notificationCollection['comment'] = $comment;
            $notificationCollection['status'] = 1;
        }

        if($notificationCollection){
            try{
                $this->_bellNotification->setData($notificationCollection)->save();
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $this->messageManager->addError($message);
                $this->messageManager->addError(
                    __('An unspecified error occurred. Please contact us for assistance.')
                );
            }
        }
    }

    public function getCustomerId()
    {
        //return $this->_customersession->getCustomerId();
        return $this->httpContext->getValue('customer_id');
    }
    
}
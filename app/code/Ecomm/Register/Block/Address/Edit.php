<?php

namespace Ecomm\Register\Block\Address;


class Edit extends \Magento\Customer\Block\Address\Edit
{
    public function getApprovelPendingOprionId()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

        $_eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

        $attribute = $_eavConfig->getAttribute('customer_address', 'address_status');
        $options = $attribute->getSource()->getAllOptions();
        $address_status = [];
        foreach ($options as $option) {
            if ($option['value'] > 0 && $option['label'] == 'Pending') {
                return $option['value'];
            }
        }
        return 0;
    }

    public function getSlThreshold(){
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $_notificationHelper = $objectManager->get('\Ecomm\Notification\Helper\Data');

        return $_notificationHelper->getSlThreshold();
    }

    public function getDeaThreshold(){
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $_notificationHelper = $objectManager->get('\Ecomm\Notification\Helper\Data');

        return $_notificationHelper->getDeaThreshold();
    }

}
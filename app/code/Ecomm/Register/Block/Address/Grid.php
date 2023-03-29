<?php

namespace Ecomm\Register\Block\Address;


class Grid extends \Magento\Customer\Block\Address\Grid
{
    public function getCustomAdditionalAddresses(): array
    {
        $additional = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('\Magento\Customer\Model\Session');

        if($customerSession->getId()) {
            $customerRepository = $objectManager->get('\Magento\Customer\Api\CustomerRepositoryInterface');
            $customerData= $customerRepository->getById($customerSession->getId());

            $_addressFactory = $objectManager->get("Magento\Customer\Model\AddressFactory");

            $primaryAddressIds = [$customerData->getDefaultBilling(), $customerData->getDefaultShipping()];

            //echo '<pre>'.print_r($primaryAddressIds, true).'</pre>';

            $addresses = $customerData->getAddresses();

            foreach ($addresses as $address) {
                //echo $address->getId().'<br />';

                if (!in_array((int)$address->getId(), $primaryAddressIds)) {

                    //echo $address->getId().'<br />';
                    $addressData = $_addressFactory->create()->load($address->getId());
                    //echo $addressData->getFirstname();
                    //echo '<pre>'.print_r($addressData->getData(), true).'</pre>';
                    $additional[] = $addressData;
                }
            }
        }
        return $additional;
    }

    public function getAddressStatus(){
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

        $_eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

        $attribute = $_eavConfig->getAttribute('customer_address', 'address_status');
        $options = $attribute->getSource()->getAllOptions();
        $address_status = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $address_status[$option['value']] = $option['label'];
            }
        }

        //echo '<pre>'.print_r($address_status, true).'</pre>';
        return $address_status;
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

}
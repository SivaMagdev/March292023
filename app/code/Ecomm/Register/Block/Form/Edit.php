<?php

namespace Ecomm\Register\Block\Form;


class Edit extends \Magento\Customer\Block\Form\Edit
{
    /*protected $_customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\SessionFactory $customerSession,
        array $data = []
    ) {
        $this->_customerSession = $customerSession->create();
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }*/

    /**
     * Get the list of regions present in the given Country
     * Returns empty array if no regions available for Country
     *
     * @param String
     * @return Array/Void
    */
    public function getRegionsOfCountry($countryCode) {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $country = $objectManager->create("\Magento\Directory\Model\Country");

        $regionCollection = $country->loadByCode($countryCode)->getRegions();
        $regions = $regionCollection->loadData()->toOptionArray(false);
        return $regions;
    }

    public function getBillingAddress(){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $_customerFactory = $objectManager->get("Magento\Customer\Model\CustomerFactory");
        $_addressFactory = $objectManager->get("Magento\Customer\Model\AddressFactory");

        //echo '<pre>'.print_r($this->customerSession->getData(), true).'</pre>';

        //echo $this->customerSession->getCustomerId();
        $customer = $_customerFactory->create()->load($this->customerSession->getCustomerId());


        $billingAddressId = $customer->getDefaultBilling();
        //echo 'billing address ID: '.$billingAddressId;
        $billingAddress = $_addressFactory->create()->load($billingAddressId);
        //echo '<pre>'.print_r($billingAddress->getData(), true).'</pre>';

        return $billingAddress->getData();
    }

    public function getShippingAddress(){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $_customerFactory = $objectManager->get("Magento\Customer\Model\CustomerFactory");
        $_addressFactory = $objectManager->get("Magento\Customer\Model\AddressFactory");

        //echo '<pre>'.print_r($this->customerSession->getData(), true).'</pre>';

        //echo $this->customerSession->getCustomerId();
        $customer = $_customerFactory->create()->load($this->customerSession->getCustomerId());


        $shippingAddressId = $customer->getDefaultShipping();
        //echo 'shipping address ID: '.$shippingAddressId;
        $shippingAddress = $_addressFactory->create()->load($shippingAddressId);
        //echo '<pre>'.print_r($shippingAddress->getData(), true).'</pre>';

        return $shippingAddress->getData();
    }

    public function getOtherShippingAddress(){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $_customerFactory = $objectManager->get("Magento\Customer\Model\CustomerFactory");
        $_addressFactory = $objectManager->get("Magento\Customer\Model\AddressFactory");
        $_addressRepository = $objectManager->get("Magento\Customer\Api\AddressRepositoryInterface");
        $searchCriteriaBuilder = $objectManager->get("Magento\Framework\Api\SearchCriteriaBuilder");

        $customer = $_customerFactory->create()->load($this->customerSession->getCustomerId());

        $address_except[] = $customer->getDefaultBilling();
        $address_except[] = $customer->getDefaultShipping();

        //echo '<pre>'.print_r($address_except, true).'</pre>';

        $addressesList = [];

        $searchCriteria = $searchCriteriaBuilder->addFilter(
            'parent_id',$this->customerSession->getCustomerId())->create();
        $addressRepository = $_addressRepository->getList($searchCriteria);
        foreach($addressRepository->getItems() as $address) {

            if(!in_array( $address->getId(), $address_except)) {

                //echo '<pre>'.print_r($address, true).'</pre>';
                //var_dump($address);
                //echo $address->getId();
                $addressesList[] = $address;
            }
        }

        //echo '<pre>'.print_r($addressesList, true).'</pre>';


        return $addressesList;
    }

    public function getMedproStatus() {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $_helper = $objectManager->create("\Ecomm\Register\Helper\Data");

        return $_helper->getStatus();
    }

    public function getGpoList(){
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

        $_eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

        $attribute = $_eavConfig->getAttribute('customer', 'partof_organization');
        $options = $attribute->getSource()->getAllOptions();
        $gpo_list = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $gpo_list[] = $option;
            }
        }

        //echo '<pre>'.print_r($gpo_list, true).'</pre>';

        /*$attribute = $_eavConfig->getAttribute('customer', 'application_status');
        $options = $attribute->getSource()->getAllOptions();
        $application_statuses = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $application_status[$option['value']] = $option['label'];
            }
        }

        echo array_search("Pending",$application_status);

        echo '<pre>'.print_r($application_status, true).'</pre>';*/

        return $gpo_list;
    }



    public function getBusinessTypes(){
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

        $_eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

        $attribute = $_eavConfig->getAttribute('customer', 'business_type');
        $options = $attribute->getSource()->getAllOptions();
        $business_types = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $business_types[] = $option;
            }
        }

        return $business_types;
    }

}
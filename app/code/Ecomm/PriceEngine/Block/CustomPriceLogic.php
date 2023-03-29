<?php

namespace Ecomm\PriceEngine\Block;

use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Ecomm\ExclusivePrice\Model\ResourceModel\ContractPrice\CollectionFactory;
use Ecomm\PriceEngine\Model\ResourceModel\ContractPrice\CollectionFactory as GpoContractPriceCollection;
use Ecomm\PriceEngine\Model\ResourceModel\ExclusivePrice\CollectionFactory as ExclusivePriceCollection;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroup;

class CustomPriceLogic extends \Magento\Framework\View\Element\Template
{

    /**
     * @var Session
     */
    private $customer;

    /**
     * @var Ecomm\ExclusivePrice\Model\ResourceModel\ContractPrice\CollectionFactory
     */
    private $contractPricecollection;

    /**
     * @var GpoContractPriceCollection
     */
    private $gpoContractPriceCollection;

    /**
     * @var ExclusivePriceCollection
     */
    public $exclusivePriceCollection;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;


    private $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $customerGroup;

    public function __construct
    (
        Session $customer,
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory $contractPricecollection,
        GpoContractPriceCollection $gpoContractPriceCollection,
        ExclusivePriceCollection $exclusivePriceCollection, 
        AddressRepositoryInterface $addressRepository,
        CustomerGroup $customerGroup

    ){
        $this->customer = $customer;
        $this->customerRepository = $customerRepository;
        $this->contractPricecollection = $contractPricecollection;
        $this->gpoContractPriceCollection = $gpoContractPriceCollection;
        $this->exclusivePriceCollection = $exclusivePriceCollection;
        $this->addressRepository = $addressRepository;
        $this->customerGroup = $customerGroup;
    }

    public function getCustomerId()
    {   
        $customer = $this->customer;
        if($customer->isLoggedIn()) {
            return $customerId = $customer->getId();
        }
    }

    public function getCustomerGroupId()
    {
        if($this->customer->isLoggedIn()) {
            return $customerGroupId = $this->customer->getCustomer()->getGroupId();
        }
    }

    public function getCustomerDishStatus($customerId)
    {   
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            return $customer->getCustomAttribute('disproportionate_hospital')->getValue();
        }
    }

    public function getContractIdWithDish($customerGroupId) {
        
        $contractIdDish = [];
        $withDish = $this->contractPricecollection->create();
        $withDish->addFieldToFilter('group_id', ['eq' => $customerGroupId])
                           ->addFieldToFilter('is_dsh', ['eq' => "1"])
                           ->addFieldToFilter('contract_type', ['eq' => 'GPO'])
                           ->addFieldToFilter('deleted', ['neq' => 1])
                           ->load();
        foreach ($withDish as $contractInfoDish) {
            $contractIdDish[] = $contractInfoDish->getContractId();
        }
        return $contractIdDish;
    }

    public function getContractIdWithoutDish($customerGroupId) {
        
        $contractIdwithoutDish = [];
        $withoutDish = $this->contractPricecollection->create();
        $withoutDish->addFieldToFilter('group_id', ['eq' => $customerGroupId])
                           ->addFieldToFilter('is_dsh', ['eq' => "0"])
                           ->addFieldToFilter('contract_type', ['eq' => 'GPO'])
                           ->addFieldToFilter('deleted', ['neq' => 1])
                           ->load();
        foreach ($withoutDish as $contractInfoDish) {
            $contractIdwithoutDish[] = $contractInfoDish->getContractId();
        }
        return $contractIdwithoutDish;
    }

    public function getRcaContractInfo($customerGroupId) {
      
        $rcaContractId = '';
        $rcaContractCollection = $this->contractPricecollection->create();
        $rcaContractCollection->addFieldToFilter('group_id', ['eq' => $customerGroupId])
                           ->addFieldToFilter('contract_type', ['eq' => 'RCA'])
                           ->addFieldToFilter('deleted', ['neq' => 1])
                           ->load();
        foreach ($rcaContractCollection as $contractInfo) {
            $rcaContractId = $contractInfo->getContractId();
        }
        return $rcaContractId;
    }

    public function getContractPriceData() {
        return $this->gpoContractPriceCollection->create();
    }

    public function getCustomRegularPrice($customerId, $_product) {
        $date = date('Y-m-d');
        $formatedDate = date('Y-m-d', strtotime($date));

        $customerInfo = $this->customerRepository->getById($customerId);
        $customerGroupId = $customerInfo->getGroupId();

        $customPriceOutput = [];
        $gpoProductPrice = '';
        $customerDishStatus = $this->getCustomerDishStatus($customerId);
        $contractIdDish = $this->getContractIdWithDish($customerGroupId);
        $contractIdWithoutDish = $this->getContractIdWithoutDish($customerGroupId);
        $rcaContractId = $this->getRcaContractInfo($customerGroupId);
       
        if ($customerDishStatus == 1) {
            $dishContractId =
            $this->getContractPriceData()->addFieldToFilter('contract_id',
            ['in'=>$contractIdDish])->addFieldToFilter('sku', ['eq' => $_product->getSku()])
            ->load();
                if($dishContractId != null){
                        foreach ($dishContractId as $testinfo) {
                        $gpoContractId = $testinfo->getContractId();
                        $gpoProductSku = $testinfo->getSku();
                        $gpoProductPrice = $testinfo->getPrice();
                        $gpoContractStatus = $testinfo->getStatus();
                    // print_r($gpoProductPrice);
                    }
                }
            }

                $withoutDishContractId =
                $this->getContractPriceData()->addFieldToFilter('contract_id',
                ['in'=>$contractIdWithoutDish])->addFieldToFilter('sku', ['eq' => $_product->getSku()])
                ->load();
        
                foreach ($withoutDishContractId as $testinfon) {
                        if($testinfon != null){
                            $gpoContractIds = $testinfon->getContractId();
                            $gpoProductSkus = $testinfon->getSku();
                            $gpoProductPrices = $testinfon->getPrice();
                            $gpoContractStatuss = $testinfon->getStatus();
                        }
        
                }

        if ($rcaContractId) {
            $rcaContract = $this->getContractPriceData()->addFieldToFilter('contract_id', ['eq'=>$rcaContractId])
            ->addFieldToFilter('sku', ['eq' => $_product->getSku()])->load();

                foreach ($rcaContract as $rcaContractInfo) {
                    $rcaContractIds = $rcaContractInfo->getContractId();
                    $rcaProductSkus = $rcaContractInfo->getSku();
                    $rcaProductPrices = $rcaContractInfo->getPrice();
                    $rcaContractStatus = $rcaContractInfo->getStatus();
                    }
        }

    $rcaOtherGroupId = $this->getOtherGroup();

    if($rcaOtherGroupId != null){

        $rcaOtherGroup = $this->getContractPriceData()->addFieldToFilter('contract_id',
        ['in'=>$rcaOtherGroupId])->addFieldToFilter('sku', ['eq' => $_product->getSku()])->load();

        foreach ($rcaOtherGroup as $rcaContractInfo) {
        
                $rcaOtherContractIds = $rcaContractInfo->getContractId();
                $rcaOtherProductSkus = $rcaContractInfo->getSku();
                $rcaOtherProductPrices = $rcaContractInfo->getPrice();
                $rcaOtherContractStatus = $rcaContractInfo->getStatus();
        }
    }

        $customerData= $this->customerRepository->getById($customerId);
        $customerSapCode = '';
        if ($customerData->getCustomAttribute('sap_customer_id') != null) {
            $customerSapCode = $customerData->getCustomAttribute('sap_customer_id')->getValue();
        }
        $collection = $this->exclusivePriceCollection->create();
        $collection = $collection->addFieldToFilter('customer_id',
    ['eq'=>$customerSapCode])->addFieldToFilter('ndc', ['eq' => $_product->getSku()])->load();

    foreach ($collection as $item) {
        $sapCustomerId = $item->getCustomerId();
        $productSku = $item->getData('ndc');
        $exclusivePrice = $item->getPrice();
        $startDate = date('Y-m-d', strtotime($item->getStartDate()));
        $endDate = date('Y-m-d', strtotime($item->getEndDate()));
    }


        $shippingAddressId = $customerData->getDefaultShipping();
        $addressInfo = $this->addressRepository->getById($shippingAddressId);
        if ($addressInfo->getCustomAttribute('hin_status')) {
            $hinStatus = $addressInfo->getCustomAttribute('hin_status')->getValue();
        } else {
            $hinStatus = '';
        }

        //  if (isset($sapCustomerId) == $customerSapCode && $_product->getSku() == $productSku && $formatedDate <= $endDate) {
        //     $customPriceOutput ['price'] = $item->getPrice();
        //     $customPriceOutput ['price_type'] = 'contract';
        // }
        if (isset($sapCustomerId) && $sapCustomerId == $customerSapCode && $_product->getSku()
        == $productSku && strtotime($formatedDate) >= strtotime($startDate) && 
        strtotime($formatedDate) <= strtotime($endDate)) {
            $customPriceOutput ['price'] = $item->getPrice();
            $customPriceOutput ['price_type'] = 'Price';

        } else if ($customerDishStatus == 1 && isset($gpoContractId) &&  in_array($gpoContractId,$contractIdDish) 
        && isset($gpoProductSku) &&  $_product->getSku() == $gpoProductSku && $gpoContractStatus == 1) {
            $customPriceOutput ['price'] = $gpoProductPrice;
            $customPriceOutput ['price_type'] = 'Price';

        } else if (isset($gpoContractIds) && isset($gpoProductSkus) && $customerDishStatus == 0 && in_array($gpoContractIds,$contractIdWithoutDish) 
        && $_product->getSku() == $gpoProductSkus && $gpoContractStatuss == 1) {
            $customPriceOutput ['price'] = $gpoProductPrices;
            $customPriceOutput ['price_type'] = 'Price';

        }
        else if (isset($gpoContractIds) && isset($gpoProductSkus) && $customerDishStatus == 1 && in_array($gpoContractIds,$contractIdWithoutDish)
        && $_product->getSku() == $gpoProductSkus && $gpoContractStatuss == 1) {        
            $customPriceOutput ['price'] = $gpoProductPrices;
            $customPriceOutput ['price_type'] = 'Price';
        }
        else if ($rcaContractId == isset($rcaContractIds) && $_product->getSku() == isset($rcaProductSkus) && $rcaContractStatus ==
            1) {
            $customPriceOutput ['price'] = $rcaProductPrices;
            $customPriceOutput ['price_type'] = 'Price';
            
        }
        else if ($rcaOtherGroupId != null && !empty($rcaOtherGroup->getData())) {
            $customPriceOutput ['price'] = $rcaOtherProductPrices;
            $customPriceOutput ['price_type'] = 'Price';
            }
         else {
            $customPriceOutput ['price'] = $_product->getPrice();
            $customPriceOutput ['price_type'] = 'Price';
        }
        
        return $customPriceOutput;
    }

    public function get340bPrice($priceIdentifier, $_product) {
        $customPrice340bOutput = [];
        $subWacPrice = $_product->getResource()->getAttribute('sub_wac')->setStoreId(0)->getFrontend()->getValue($_product);
        $phsPrice = $_product->getResource()->getAttribute('phs_indirect')->setStoreId(0)->getFrontend()->getValue($_product);
        $mrpPrice = $_product->getResource()->getAttribute('price')->setStoreId(0)->getFrontend()->getValue($_product);
        // if ($priceIdentifier == "sub_wac" && $_product->getResource()->getAttribute('sub_wac')->setStoreId(0)->getFrontend()->getValue($_product)) {
        if ($priceIdentifier == "sub_wac" && $subWacPrice) {
            $customPrice340bOutput['price'] = $subWacPrice;
            $customPrice340bOutput['price_type'] = "340b(Sub-WAC Price)";
        }else if ($priceIdentifier == "sub_wac" && !$subWacPrice) {
            $customPrice340bOutput['price'] = $mrpPrice;
            $customPrice340bOutput['price_type'] = "340b(Sub-WAC Price)";
        }
        // if ($priceIdentifier == "phs_indirect" && $_product->getResource()->getAttribute('phs_indirect')->setStoreId(0)->getFrontend()->getValue($_product)) {
        if ($priceIdentifier == "phs_indirect" && $phsPrice) {
            $customPrice340bOutput['price'] = $phsPrice;
            $customPrice340bOutput['price_type'] = "340b(Phs Indirect Price)";
        }else if ($priceIdentifier == "phs_indirect" && !$phsPrice) {
            $customPrice340bOutput['price'] = $mrpPrice;
            $customPrice340bOutput['price_type'] = "340b(Phs Indirect Price)";
        }

        return $customPrice340bOutput;
    }

    public function getOtherGroup(){
        $id = null;
        $customerGroups = $this->customerGroup->toOptionArray();
        foreach($customerGroups as $group){
            if($group['label'] == 'Others GPOs'){
                $id = $group['value'];
            }
        }
        $rcaContractId = null;
        if($id != null){

            $rcaContractCollection = $this->contractPricecollection->create();
            $rcaContractCollection->addFieldToFilter('group_id', ['eq' => $id])
                               ->addFieldToFilter('contract_type', ['eq' => 'RCA'])
                               ->addFieldToFilter('deleted', ['neq' => 1])
                               ->load();
            foreach ($rcaContractCollection as $contractInfo) {
                $rcaContractId = $contractInfo->getContractId();
            }
            return $rcaContractId;
    
        }
        return $rcaContractId;
    }

}
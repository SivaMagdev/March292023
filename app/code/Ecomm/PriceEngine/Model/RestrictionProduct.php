<?php
namespace Ecomm\PriceEngine\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as SalesOrderCollection;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;

class RestrictionProduct{


    protected $salesOrderCollection;
    private $itemFactory;
    private $customerRepository;
    private $defualtShippingAddress;
    private $customer;

    public function __construct(   
        SalesOrderCollection $salesOrderCollection,
        ItemFactory $itemFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $defualtShippingAddress){
          
            $this->salesOrderCollection = $salesOrderCollection;
            $this->itemFactory = $itemFactory;
            $this->customerRepository = $customerRepository;
            $this->defualtShippingAddress = $defualtShippingAddress;
        }


        public function productRestrictions($product, $qty, $customerId){
            $output = [];
            $startDate = $product->getResource()->getAttribute('restriction_date')->setStoreId(0)->getFrontend()->getValue($product);
            $totalPeriods = (int) $product->getResource()->getAttribute('total_day')->setStoreId(0)->getFrontend()->getValue($product);
            $restrictionDate = $product->getResource()->getAttribute('periods')->setStoreId(0)->getFrontend()->getValue($product);
            $productCount = (int) $product->getResource()->getAttribute('product_limits')->setStoreId(0)->getFrontend()->getValue($product);
            // if($qty > $productCount){
            //     $output['message'] = 'This NDC is under quantity restriction. At this point,  '.$productCount.' qty can be added.  To order more quantities , please contact administrator or customer service';
            //     $output['qty'] =  $productCount;
            //      return $output; 

            // }
            $today = date('d-m-Y');
        if($startDate == null || $startDate == '' || $totalPeriods == null || $totalPeriods == '' || 
        $restrictionDate == null || $restrictionDate == '' || $productCount == null || $productCount == ''){
            
            return $output;
        }else{

            $formatedDate = date('d-m-Y', strtotime($startDate));
            $from = $this->getInDays($formatedDate,$today, $restrictionDate, $totalPeriods);
            $to = date('Y-m-d H:i:s');
            
            $addressId = $this->defualtShippingAddress->getDefaultShippingAddress($customerId);
            $data = $this->salesOrderCollection->create()
            ->addFieldToFilter('created_at', ['lteq'=> $to])
            ->addFieldToFilter('created_at', ['gteq'=>$from])
            ->addFieldToFilter('customer_id',$customerId);
            $totalQty = 0;
            foreach($data as $list){
               
                if($list->getShippingAddress()->getData('customer_address_id') == $addressId->getId()){
                    foreach($list->getItems() as $items){
                        if($items->getSku() == $product->getSku()){
                            $totalQty += $items->getData('qty_ordered');
                        }
                    }
                }
            }
            $chckQty =(int) $totalQty+$qty;
            
            if($chckQty > $productCount){
                $available = $productCount-$totalQty;
                $output['message'] = 'This NDC is under quantity restriction. At this point,  '.$available.' qty can be added.  To order more quantities , please contact administrator or customer service';
                $output['qty'] =  $available;
                
                 return $output; 
            }
        }
    }

    private function getInDays($startDate,$today, $restrictionDate, $totalPeriods){
        $def = 0;
        if($restrictionDate == 'Weeks'){
            $def = 7*$totalPeriods;
        }
        if($def != 0){
            $filterDate = date_diff(date_create($startDate),date_create($today))->format("%a");
            if($filterDate > $def){
                $numberOfDays = -($filterDate%$def);   
            }else{
                $numberOfDays = -$filterDate;
            }
            return  date("Y-m-d H:i:s", strtotime($numberOfDays." days"));
        }
    }

    }
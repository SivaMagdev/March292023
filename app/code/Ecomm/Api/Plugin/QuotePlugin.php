<?php

/**
 * Copyright © 2018 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecomm\Api\Plugin;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\InputException;
use Ecomm\PriceEngine\Model\RestrictionProduct;
use Magento\Catalog\Model\ProductRepository;
use Ecomm\PriceEngine\Block\CustomPriceLogic;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;

class QuotePlugin {

    /**
     * @var \Magento\Quote\Api\Data\CartItemExtensionFactory
     */
    protected $cartItemExtension;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Authorization\Model\CompositeUserContext
     */
    protected $userContext;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    private $modelProduct;

    private $modelProductOption;

    private $checkoutSession;

    protected $_date;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var RestrictionProduct
     */
    private $restrictionProduct;

    /**
     * @var CustomPriceLogic
     */
    private $customPriceLogic;
    private $defualtShippingAddress;
    private $hinStatus = 0;
    protected $extensionFactory;

    /**
     * @param \Magento\Quote\Api\Data\CartItemExtensionFactory $cartItemExtension
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param RestrictionProduct $restrictionProduct
     * @param CustomPriceLogic $customPriceLogic
     */
    public function __construct(
        \Magento\Quote\Api\Data\CartItemExtensionFactory $cartItemExtension,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepository,
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $salableQuantity,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $_productTypeConfigurable,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Catalog\Helper\Output $catalogHelper,
        \Magento\Catalog\Model\Product $modelProduct,
        \Magento\Catalog\Model\Product\Option $modelProductOption,
        \Magento\Checkout\Model\Session $checkoutSession,
        CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Authorization\Model\CompositeUserContext $userContext,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        LoggerInterface $logger,
        RestrictionProduct $restrictionProduct,
        CustomPriceLogic $customPriceLogic,
        AccountManagementInterface $defualtShippingAddress,
        CartExtensionFactory $extensionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->cartItemExtension        = $cartItemExtension;
        $this->productRepository        = $productRepository;
        $this->salableQuantity          = $salableQuantity;
        $this->_productTypeConfigurable = $_productTypeConfigurable;
        $this->urlInterface             = $urlInterface;
        $this->catalogHelper            = $catalogHelper;
        $this->modelProduct             = $modelProduct;
        $this->modelProductOption       = $modelProductOption;
        $this->checkoutSession          = $checkoutSession;
        $this->customerRepositoryInterface          = $customerRepositoryInterface;
        $this->userContext              = $userContext;
        $this->_date                    = $date;
        $this->_logger                  = $logger;
        $this->restrictionProduct       = $restrictionProduct;
        $this->customPriceLogic         = $customPriceLogic;
        $this->defualtShippingAddress   = $defualtShippingAddress;
        $this->extensionFactory         = $extensionFactory;
        $this->scopeConfig              = $scopeConfig;
        $this->storeManager             = $storeManager;
        $this->timezone = $timezone;

    }

    /**
     * Add attribute values
     *
     * @param   \Magento\Quote\Api\CartRepositoryInterface $subject,
     * @param   $quote
     * @return  $quoteData
     */
    public function afterGetActive(
    \Magento\Quote\Api\CartRepositoryInterface $subject, $quote
    ) {

        $quoteData = $this->setAttributeValue($quote);
        return $quoteData;
    }

    /**
     * Add attribute values
     *
     * @param   \Magento\Quote\Api\CartRepositoryInterface $subject,
     * @param   $quote
     * @return  $quoteData
     */
    public function afterGetActiveForCustomer(
    \Magento\Quote\Api\CartRepositoryInterface $subject, $quote
    ) {
        $quoteData = $this->setAttributeValue($quote);
        return $quoteData;
    }

    /**
     * set value of attributes
     *
     * @param   $product,
     * @return  $extensionAttributes
     */
    private function setAttributeValue($quote) {
  
        $data = [];
        if ($quote->getItems() && $quote->getItemsCount() && str_contains($this->urlInterface->getCurrentUrl(), 'rest/V1/carts/mine/items') == null && str_contains($this->urlInterface->getCurrentUrl(), 'rest/V1/carts') != null) {
            foreach ($quote->getItems() as $item){
                $quoteitem = $quote->getItemById($item->getId());

                //echo ''.$quoteitem->getPrice().' - '.$quoteitem->getCustomPrice();
                $options = $quoteitem->getProduct()->getTypeInstance(true)->getOrderOptions($quoteitem->getProduct());

                //echo '<pre>'.print_r($options, true).'</pre>';
                //$this->_logger->info('Item Price:'.$quoteitem->getPrice().'-'.$quoteitem->getCustomPrice());
                $customOptions = [];
                if(isset($options['options'])){
                    $customOptions = $options['options'];
                    //$this->_logger->info('Item Options:'.print_r($customOptions, true));
                }

                $product = $this->modelProduct->load($quoteitem->getProductId());

                $salable_quantities = 0;

                if (!empty($customOptions)) {

                    $stockQty = 0;
                    $option_value = 0;
                    $custom_option_price = 0;

                    foreach($customOptions as $customOption){
                        $option_value = $customOption['option_value'];
                    }

                    //echo $option_value;

                    $custom_option_ids = [];

                    $delete_this_product = false;
                    $customOptions2 = $this->modelProductOption->getProductOptionCollection($product);

                    foreach($customOptions2 as $option) {
                        $values = $option->getValues();

                        //loop all child options
                        foreach($values as $value) {
                            //echo '-'.$value->getOptionTypeId();
                            $custom_option_ids[] = $value->getOptionTypeId();
                            if($option_value == $value->getOptionTypeId()) {

                                //echo '<pre>'.print_r($value->getData(), true).'</pre>';
                                $expiry_date = $value->getExpiryDate();
                                //echo $expiry_date.'<br />';

                                $current_date = strtotime($this->_date->date('Y-m-d H:i:s'));
                                $expiry_date = strtotime($expiry_date);
                                if($expiry_date <=$current_date){

                                    //echo 'delete';
                                    $delete_this_product = true;

                                }
                                $stockQty = $value->getQuantity();

                                $salable_quantities = $value->getQuantity();

                                $custom_option_price = $value->getDefaultPrice();

                                //echo $custom_option_price;

                                //$this->logger->info('Option Qty:'.$value->getQuantity());
                                //$this->_logger->info('Option Price :'.$custom_option_price);

                            }
                            //$this->_logger->info('Item Options-2:'.json_encode($value->getData()));
                        }
                    }

                    if($delete_this_product == true || !in_array($option_value, $custom_option_ids)) {

                        $quoteitem->delete();//deletes the item

                        $quote->collectTotals()->save();

                    } else {
                        if($custom_option_price > 0) {
                            //$customprice = $this->checkoutSession->getQuote()->getItemById($quoteitem->getId());
                            $quoteitem->setPrice($custom_option_price);
                            $quoteitem->setBasePrice($custom_option_price);
                            $quoteitem->setCustomPrice($custom_option_price);
                            $quoteitem->setOriginalCustomPrice($custom_option_price);
                            $quoteitem->getProduct()->setIsSuperMode(true);
                            $quoteitem->setBaseRowTotal($custom_option_price*$item->getQty());
                            $quoteitem->setRowTotal($custom_option_price*$item->getQty());
                            $quoteitem->save();

                            $quote->collectTotals()->save();

                        }
                    }

                } else {

                    $tierPrices = $product->getData('tier_price');
                    //$this->logger->info('Tier price List:'.print_r($tierPrices, true));

                    $group_prices = [];
                    $group_price = 0;
                    if(is_array($tierPrices)) {
                        foreach($tierPrices as $tierPrice){
                            $group_prices[$tierPrice['cust_group']] = $tierPrice['price'];
                        }
                    }
                    $customerInfo = $this->customerRepositoryInterface->getById($this->userContext->getUserId());
                    $customerGroupId = $customerInfo->getGroupId();


                    if(isset($group_prices[$customerGroupId]) && $group_prices[$customerGroupId] > 0){
                        $group_price = $group_prices[$customerGroupId];
                    }

                    //echo 'group_price:'.$group_price;
                    if($group_price > 0){

                        $quoteitem->setPrice($group_price);
                        $quoteitem->setBasePrice($group_price);
                        $quoteitem->setCustomPrice($group_price);
                        $quoteitem->setOriginalCustomPrice($group_price);
                        $quoteitem->getProduct()->setIsSuperMode(true);
                        $quoteitem->setBaseRowTotal($group_price*$item->getQty());
                        $quoteitem->setRowTotal($group_price*$item->getQty());
                        $quoteitem->save();

                        $quote->collectTotals()->save();

                    } else {

                        if($quoteitem->getCustomPrice() <=  0 && $quoteitem->getPrice() <= 0){

                            $quoteitem->delete();//deletes the item

                            $quote->collectTotals()->save();
                        }
                    }

                }
            }
        }

        if ($quote->getItems() && $quote->getItemsCount() && str_contains($this->urlInterface->getCurrentUrl(), 'rest/V1/carts/mine/items') == null && str_contains($this->urlInterface->getCurrentUrl(), 'rest/V1/carts') != null) {
            //die;
            $defualtShippingAddress =  $this->defualtShippingAddress->getDefaultShippingAddress($this->userContext->getUserId());
            $hinStatus = $defualtShippingAddress->getCustomAttribute('hin_status')->getValue();

            foreach ($quote->getItems() as $item){
               
                $quoteitem = $quote->getItemById($item->getId());

                $salable_quantities = 0;
                $options = $quoteitem->getProduct()->getTypeInstance(true)->getOrderOptions($quoteitem->getProduct());

                //echo '<pre>'.print_r($options, true).'</pre>';
                $customOptions = [];
                if(isset($options['options'])){
                    $customOptions = $options['options'];
                    //$this->_logger->info('Item Options:'.print_r($customOptions, true));
                }

                $product = $this->modelProduct->load($quoteitem->getProductId());

                if (!empty($customOptions)) {

                    $stockQty = 0;
                    $option_value = 0;
                    $custom_option_price = 0;

                    foreach($customOptions as $customOption){
                        $option_value = $customOption['option_value'];
                    }

                    //echo $option_value;

                    $custom_option_ids = [];
                    $customOptions2 = $this->modelProductOption->getProductOptionCollection($product);

                    foreach($customOptions2 as $option) {
                        $values = $option->getValues();

                        //loop all child options
                        foreach($values as $value) {
                            //echo '-'.$value->getOptionTypeId();
                            $custom_option_ids[] = $value->getOptionTypeId();
                            if($option_value == $value->getOptionTypeId()) {

                                $salable_quantities = $value->getQuantity();

                            }
                        }
                    }

                }

                //echo $salable_quantities;

                $reservationTimerMode = $this->scopeConfig->getValue("prcr/general/timer_mode",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->storeManager->getStore()->getStoreId());

                if ($reservationTimerMode == '1') {
                    $timerMode = "Global timer";
                } else {
                    $timerMode = "Separate timers";
                }

                $extensionAttributeTimer = $quote->getExtensionAttributes();
                $extensionAttributeTimer = $extensionAttributeTimer ? $extensionAttributeTimer : $this->extensionFactory->create();
                $extensionAttributeTimer->setData('timer_mode',$timerMode);
                $quote->setExtensionAttributes($extensionAttributeTimer);

                $data = [];
                $extensionAttributes = $item->getExtensionAttributes();
                if ($extensionAttributes === null) {
                    $extensionAttributes = $this->cartItemExtension->create();
                }
                if($item->getSku()){
                    $productData = $this->productRepository->create()->get($item->getSku());

                    $extensionAttributes->setImage($productData->getThumbnail());
                    $extensionAttributes->setStrength($this->catalogHelper->productAttribute($productData, $productData->getStrength(), 'strength'));
                    $extensionAttributes->setPacksize($this->catalogHelper->productAttribute($productData, $productData->getPackSize(), 'pack_size'));
                    $extensionAttributes->setCasepack($this->catalogHelper->productAttribute($productData, $productData->getCasePack(), 'case_pack'));
                    $extensionAttributes->setColdchain($this->catalogHelper->productAttribute($productData, $productData->getColdChain(), 'cold_chain'));
                    if ($item->getTimerExpireAt()) {
                        $cartExpiryTime = date('Y/m/d H:i:s', $item->getTimerExpireAt());
                        $dateTimeZone = $this->timezone->date(new \DateTime($cartExpiryTime))->format('Y/m/d H:i:s');
                        $extensionAttributes->setTimerExpireAt($dateTimeZone);
                    }

                    $currentStoreTime = $this->timezone->date()->format('Y-m-d H:i:s');
                    $extensionAttributes->setCurrentStoreTime($currentStoreTime);

                    $regularPrice = $this->customPriceLogic->getCustomRegularPrice($quote->getCustomerId(), $productData);
                
                    $sub = $this->customPriceLogic->get340bPrice("sub_wac", $productData);
                    $phs = $this->customPriceLogic->get340bPrice("phs_indirect", $productData);


                    if(!empty($regularPrice)){
                        $extensionAttributes->setRegularPrice( '$' . number_format($regularPrice['price'], 2));
                        $extensionAttributes->setPriceType($regularPrice['price_type']);
                    }

                    if($hinStatus == 1 && !empty($sub)){
                        $extensionAttributes->setsubwacPrice('$ '.$sub['price']);
                        $extensionAttributes->setPriceType($sub['price_type']);
                    }

                    if($hinStatus == 1 && !empty($phs)){
                        $extensionAttributes->setphsPrice('$ '.$phs['price']);
                        $extensionAttributes->setPriceType($phs['price_type']);
                    }
                    if($hinStatus == 1){
                    
                        if($quoteitem->getData('price_type') == 'Price'){
                         
                            $quoteitem->setCustomPrice($regularPrice['price']);
                            $quoteitem->setOriginalCustomPrice($regularPrice['price']);
                            $quoteitem->setData('price_type',$regularPrice['price_type']);
                            
                        }else if($quoteitem->getData('price_type') == '340b(Sub-WAC Price)'){
                            $quoteitem->setCustomPrice($sub['price']);
                            $quoteitem->setOriginalCustomPrice($sub['price']);
                            $quoteitem->setData('price_type',$sub['price_type']);
                              
                        }else if($quoteitem->getData('price_type') == '340b(Phs Indirect Price)'){
                            $quoteitem->setCustomPrice($phs['price']);
                            $quoteitem->setOriginalCustomPrice($phs['price']);
                            $quoteitem->setData('price_type',$phs['price_type']);
                                
                        }
                    }else {
                        $quoteitem->setCustomPrice($regularPrice['price']);
                        $quoteitem->setOriginalCustomPrice($regularPrice['price']);
                        $quoteitem->setData('price_type',$regularPrice['price_type']);
                  
                    }

                    //$salable_quantities = $this->salableQuantity->execute($item->getSku());
                    //$extensionAttributes->setSalablequantity($salable_quantities[0]['qty']);

                    if($salable_quantities == 0){
                        $salable_quantities = $this->salableQuantity->execute($item->getSku());
                        $extensionAttributes->setSalablequantity($salable_quantities[0]['qty']);
                    } else {
                        $extensionAttributes->setSalablequantity($salable_quantities);
                    }

                    //set configurable product Id
                    // $parent_id = $this->_productTypeConfigurable->getParentIdsByChild($item->getProductId());
                    // if($parent_id){
                    //     $extensionAttributes->setParentid($parent_id);
                    // }

                    // get custom option
                    if($item->getProduct() !== null){
                        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                        if(isset($options['options'])){
                            $extensionAttributes->setCustomoptions($options['options']);
                        }
                    }

                    $item->setExtensionAttributes($extensionAttributes);
                    $quoteitem->save();
                    $quote->collectTotals()->save();
                }

            }
        }
        // if($quote->getItems()){
        //     $customerId = $quote->getCustomerId();
        //     foreach($quote->getItems() as $item){
        //         $extensionAttributes = $item->getExtensionAttributes();
        //         if ($extensionAttributes === null) {
        //             $extensionAttributes = $this->cartItemExtension->create();
        //         }
        //         $validation = $this->itemValidation($item, $customerId);
        //         if(!empty($validation)){
        //             $extensionAttributes->setError($validation['message']);
        //             $item->setQty($validation['qty']);
        //         }
        //         $item->setExtensionAttributes($extensionAttributes);
        //     }
        // }

        return $quote;
    }

    public function itemValidation($product, $customerId){
        $pro =  $this->productRepository->create()->get($product->getSku());
        $res = $this->restrictionProduct->productRestrictions(
            $pro,$product->getQty(), $customerId);
            if(!empty($res)){
               return $res;
            }
        return [];
    }

}


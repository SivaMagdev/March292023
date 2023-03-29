<?php

/**
 * Copyright © 2018 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecomm\CustomDiscount\Plugin;

use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;



class BestCouponPlugin {

    /**
     * @var \Magento\Quote\Api\Data\CartItemExtensionFactory
     */
    protected $cartItemExtension;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    private $modelProduct;

    private $modelProductOption;

    private $checkoutSession;

    protected $_date;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Quote\Api\Data\CartItemExtensionFactory $cartItemExtension
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
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
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        LoggerInterface $logger
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
        $this->_date                    = $date;
        $this->logger               = $logger;
    }

    /**
     * Add attribute values
     *
     * @param   \Magento\Quote\Api\CartRepositoryInterface $subject,
     * @param   $quote
     * @return  $quoteData
     */
    public function afterGet(
    \Magento\Quote\Api\CartRepositoryInterface $subject, $quote
    ) {
        $quoteData = $this->setBestDiscount($quote);
        return $quoteData;
    }

    /**
     * set best discount for the users
     *
     * @param  $quote
     */
    private function setBestDiscount($quote) {
        $data = [];
        //$this->logger->info('Plugin:'.$this->urlInterface->getCurrentUrl());
        //$this->logger->info('Plugin quote getItemsCount:'.$quote->getItemsCount());
        if ($quote->getItems() && $quote->getItemsCount() && str_contains($this->urlInterface->getCurrentUrl(), 'rest/V1/carts/mine/items') == null && str_contains($this->urlInterface->getCurrentUrl(), 'rest/V1/carts') != null) {

            //$this->logger->info('Test IF:'.$quote->getItemsCount());

            $quoteid = $quote->getId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helper = $objectManager->create('Ecomm\CustomDiscount\Helper\Data');
            $discountAmount = 0;
            if($quoteid) {
                $total=$quote->getBaseSubtotal();
                $canAddItems = $quote->isVirtual()? ('billing') : ('shipping'); 

                //echo $quote->getAddress()->getShippingAmount();

                //print_r($quote->getShippingAddress()->getData());

                foreach ($quote->getAllAddresses() as $address) {
                    if ($address->getAddressType() == 'shipping') {
                        $rules= $helper->getBestRuleDetails($quote,$total,$address);
                        if (!empty($rules)) {
                            /*$quote->setAppliedRuleIds('');
                            $quote->setAppliedRuleIds($rules['rule_id']);
                            $quote->save();
                            $address->setAppliedRuleIds('');
                            $address->setAppliedRuleIds($rules['rule_id']);
                            $address->save();*/
                            //$this->logger->info('Plugin:'.print_r($rules, true));
                            $discountAmount = $rules['rule_amount'];
                            if ($rules['free_shipping']) {
                                $address->setShippingDiscountAmount($discountAmount);
                                $address->setBaseShippingDiscountAmount($discountAmount);
                            } else {
                            // $address->setBaseSubTotal($quote->getBaseSubTotal() - $discountAmount);
                                $address->setShippingDiscountAmount(0);
                                $address->setBaseShippingDiscountAmount(0);
                            }

                            $rule = $objectManager->create('Magento\SalesRule\Model\Rule')->load($rules['rule_id']);
                            $address->setDiscountDescription($rule->getName());
                            $address->setGrandTotal($total - $discountAmount + $address->getShippingAmount());
                            $address->setBaseGrandTotal($total - $discountAmount + $address->getShippingAmount());
                            $address->setSubtotalWithDiscount($total - $discountAmount + $address->getShippingAmount());
                            $address->setBaseSubtotalWithDiscount($total - $discountAmount + $address->getShippingAmount());
                            $address->setDiscountAmount(-($discountAmount));
                            $address->setBaseDiscountAmount(-($discountAmount));


                        //  $address->setAppliedRuleIds($rules['rule_id']);
                        // $quote->setAppliedRuleIds($rules['rule_id']);
                            if(!$rules['free_shipping']) {
                                $item_discount = 0;
                                foreach($quote->getAllItems() as $item){
                                    $item_discount = $helper->getItemDiscounts($item,$rule);

                                    if(isset($item_discount['discount_amount']))
                                        $discountAmount = $item_discount['discount_amount'];

                                    $item->setDiscountAmount($discountAmount);
                                    $item->setBaseDiscountAmount($discountAmount);
                                    $item->setOriginalDiscountAmount($discountAmount);
                                    $item->setBaseOriginalDiscountAmount($discountAmount)->save();
                                }
                            }
                        } else {
                            $discountAmount = 0;
                            $address->setGrandTotal($total - $discountAmount + $address->getShippingAmount());
                            $address->setShippingDiscountAmount(0);
                            $address->setShippingDiscountAmount(0);
                            $address->setDiscountAmount(-($discountAmount));
                            $address->setBaseDiscountAmount(-($discountAmount));
                        }
                        //echo $address->getAddressType();
                    } else {
                        //$rules= $helper->getBestRuleDetails($quote,$total,$address);

                        $address =  $quote->getShippingAddress();
                        $rules= $helper->getBestRuleDetails($quote,$total,$address);
                        if(!empty($rules))
                        {
                            /*$quote->setAppliedRuleIds('');
                            $quote->setAppliedRuleIds($rules['rule_id']);
                            $quote->save();
                            $address->setAppliedRuleIds('');
                            $address->setAppliedRuleIds($rules['rule_id']);
                            $address->save();*/

                            $discountAmount = $rules['rule_amount'];
                            if ($rules['free_shipping']) {
                                $address->setShippingDiscountAmount($discountAmount);
                                $address->setBaseShippingDiscountAmount($discountAmount);
                            } else {
                            // $address->setBaseSubTotal($quote->getBaseSubTotal() - $discountAmount);
                                $address->setShippingDiscountAmount(0);
                                $address->setBaseShippingDiscountAmount(0);
                            }

                            $rule = $objectManager->create('Magento\SalesRule\Model\Rule')->load($rules['rule_id']);

                            $address->setDiscountDescription($rule->getName());
                            $address->setGrandTotal($total - $discountAmount + $address->getShippingAmount());
                            $address->setBaseGrandTotal($total - $discountAmount + $address->getShippingAmount());
                            $address->setSubtotalWithDiscount($total - $discountAmount + $address->getShippingAmount());
                            $address->setBaseSubtotalWithDiscount($total - $discountAmount + $address->getShippingAmount());
                            $address->setDiscountAmount(-($discountAmount));
                            $address->setBaseDiscountAmount(-($discountAmount));


                        //  $address->setAppliedRuleIds($rules['rule_id']);
                        // $quote->setAppliedRuleIds($rules['rule_id']);
                            if(!$rules['free_shipping']) {
                                $item_discount = 0;
                                foreach ($quote->getAllItems() as $item) {
                                    $item_discount = $helper->getItemDiscounts($item,$rule);

                                    if(isset($item_discount['discount_amount']))
                                        $discountAmount = $item_discount['discount_amount'];

                                    $item->setDiscountAmount($discountAmount);
                                    $item->setBaseDiscountAmount($discountAmount);
                                    $item->setOriginalDiscountAmount($discountAmount);
                                    $item->setBaseOriginalDiscountAmount($discountAmount)->save();
                                }
                            }
                        } else {
                            $discountAmount = 0;
                            $address->setGrandTotal($total - $discountAmount + $address->getShippingAmount());
                            $address->setShippingDiscountAmount(0);
                            $address->setShippingDiscountAmount(0);
                            $address->setDiscountAmount(-($discountAmount));
                            $address->setBaseDiscountAmount(-($discountAmount));
                        }
                    }
     
                  }//end: if
                } //end: foreach
                //echo $quote->getGrandTotal();
        }
        return $quote;
    }

}
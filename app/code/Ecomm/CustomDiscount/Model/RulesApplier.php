<?php

namespace Ecomm\CustomDiscount\Model;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class RulesApplier extends \Magento\SalesRule\Model\RulesApplier
{


    public $final_rule_id;

    protected function applyRule($item, $rule, $address, $couponCode)
    {


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $helper = $objectManager->create('Ecomm\CustomDiscount\Helper\Data');
        $request = $objectManager->create('Magento\Framework\App\RequestInterface');
        $logger = $objectManager->create('\Psr\Log\LoggerInterface');

        //$couponCode = $request->getPost('remove') == 1 ? '' : trim($request->getPost('coupon_code'));
        //$logger->info('Model:');
        $subtotal = $address->getBaseSubtotal();

        $couponCode = $item->getQuote()->getCouponCode();

        $quote = $item->getQuote();

        $totalitems = $item->getTotalQty();
        
        $totalOrderCount = $helper->getTotalOrders($quote);  

        $res = $customer_rules = $other_rules = [];

        $final_rule_id = $rule->getId();

        $totalproducts = $item->getQuote()->getItemsCount();

        //$totalproducts =  count($items);
        
        $final_rules_arr = [];

       
        if(empty($couponCode)){
            if($subtotal > 0)
            {
               
                 //getting all the active rules that are applicable customer segment wise or for others
               
                $rules = $helper->getAllRules($couponCode);
                foreach($rules as $ruleval)
                {   
                    $result = $helper->checkConditions($totalOrderCount,$ruleval->getId());
                
                    if(!empty($result))
                        $res[$ruleval->getId()] = $helper->checkConditions($totalOrderCount,$ruleval->getId());
                }

                //creating different arrays to seggregate the customer segment rules and the other rules
                if(!empty($res))
                {
                    foreach($res as $rulekey => $ruleval)
                    {
                        if(isset($ruleval['others']) && !isset($ruleval['customer']))
                            $other_rules[] = $ruleval['others'];
    
                        if(isset($ruleval['customer']))
                            $customer_rules[] = $ruleval['customer'];
                        
                    }
                }
                
    
                $all_rules = array_merge($other_rules,$customer_rules);
    
                $final_rules=[];

                //print_r($all_rules);

            
                //Finding the Best Rule
    
                if(!empty($all_rules))
                {
                    
                    $final_rules_arr = $helper->getBestRule($item,$address,$all_rules,$couponCode);     
                    
                    //print_r($final_rules_arr);
                    
                    if(!empty($final_rules_arr))
                    {
                        $this->final_rules_arr = $final_rules_arr;
                        $final_rule_id = $final_rules_arr['rule_id'];
                        $final_rules[] = $final_rules_arr['rule_id'];
                        $this->final_rule_id = $final_rule_id;
                    
                    
                    }
                    
                }
            
                $rule = $objectManager->create('Magento\SalesRule\Model\Rule')->load($final_rule_id);
                
                $item->getQuote()->setAppliedRuleIds($final_rule_id);
                $address->setAppliedRuleIds($final_rule_id);
                //$this->setAppliedRuleIds($item, $final_rules);
            }
        }
        else
        {
            $rules = $helper->getAllRules($couponCode);
            foreach($rules as $ruleval)
            {   
                $final_rule_id = $ruleval->getId();
    
            }
            $rule = $objectManager->create('Magento\SalesRule\Model\Rule')->load($final_rule_id);

            $getRuleData = $rule->getData();

            $ruleres = $helper->getConditions($subtotal,$getRuleData,$totalitems);

            $final_rules_arr['rule_id'] =  $final_rule_id;
            $final_rules_arr['rule_amount'] =  $ruleres[$final_rule_id];
            $final_rules_arr['free_shipping'] = 0;
            $final_rules_arr['coupon_code'] = $couponCode;

            $this->final_rules_arr = $final_rules_arr;
            
        }
        

        if ($item->getChildren() && $item->isChildrenCalculated()) {
            $cloneItem = clone $item;
            /**
             * validate without children
             */
            $applyAll = $rule->getActions()->validate($cloneItem);
            foreach ($item->getChildren() as $childItem) {
                if ($applyAll || $rule->getActions()->validate($childItem)) {
                    $discountData = $this->getDiscountData($childItem, $rule, $address);
                    $this->setDiscountData($discountData, $childItem);
                }
            }
        } else {
            $discountData = $this->getDiscountData($item, $rule, $address);
            $this->setDiscountData($discountData, $item);
        }

        $this->maintainAddressCouponCode($address, $rule, $couponCode);
        $this->addDiscountDescription($address, $rule);
       
        return $this;
    
    }

    

    public function addDiscountDescription($address, $rule)
    {
       
        
        $description = $address->getDiscountDescriptionArray();
        $ruleLabel = $rule->getStoreLabel($address->getQuote()->getStore());
        $label = '';
        if ($ruleLabel) {
            $label = $ruleLabel;
        } else {
            if (strlen($address->getCouponCode())) {
                $label = $address->getCouponCode();

                if ($rule->getDescription()) {
                    $label = $rule->getDescription();
                }
            }
        }

        //echo $this->final_rule_id;

        if(isset($this->final_rule_id) && $this->final_rule_id == $rule->getId()) {
            if (strlen($label)) {
                $description[$rule->getId()] = $label;
            }
        }

        if(isset($this->final_rules_arr['coupon_code']))
            $description[$this->final_rule_id] = $this->final_rules_arr['coupon_code'];

        

        /*if(isset($this->final_rule_id) && $this->final_rule_id == $rule->getId()) {
            if (strlen($label)) {
                $description[$rule->getId()] = $label;
            }
        }
        else
        {
            if (strlen($label)) {
                $description[$rule->getId()] = $label;
            }
        }*/


        /*if (strlen($label)) {
            $description[$rule->getId()] = $label;
        }*/

        //print_r($description);
        //die("sdf");

        $address->setDiscountDescriptionArray($description);
       

        return $this;
    }


    protected function setDiscountData($discountData, $item)
    {
       
        
        $final_rules_arr = [];
	$address = $item->getAddress();

	$shipping_amount = $address->getShippingAmount();
	$discount = $address->getDiscountAmount();

 $objectManager = \Magento\Framework\App\ObjectManager::getInstance();



        
        if(isset($this->final_rules_arr))
            $final_rules_arr =$this->final_rules_arr;

       
        if(!empty($final_rules_arr))
        {

	    $items = $item->getQuote()->getAllItems();            
        $totalproducts =  count($items);


        if($final_rules_arr['free_shipping'])
	        $discount_amt =  0;
        else
            $discount_amt =  $discountData->getAmount();

	    $auto_calculated_discount = abs($discount);

	    $discount_with_shipping = $final_rules_arr['rule_amount'] + $shipping_amount;
		
        if(round($auto_calculated_discount,3) ==  round($discount_with_shipping,3))
	    {
            //$discount_amt =  ($final_rules_arr['rule_amount']/$totalproducts) - ($shipping_amount/$totalproducts);

            $discount_amt = $discountData->getAmount();

            $calculator =  $objectManager->create('\Magento\SalesRule\Model\Validator');

            $calculator->reset($address);

            $address->setShippingDiscountAmount($shipping_amount);
            $address->setBaseShippingDiscountAmount($shipping_amount);
            
            $calculator->processShippingAmount($address);

	    }

        if($final_rules_arr['free_shipping']){
            $calculator =  $objectManager->create('\Magento\SalesRule\Model\Validator');

            $calculator->reset($address);

            $address->setShippingDiscountAmount($shipping_amount);
            $address->setBaseShippingDiscountAmount($shipping_amount);
            
            $calculator->processShippingAmount($address);
        }
		
       
           
	        $item->setDiscountAmount($discount_amt);
            $item->setBaseDiscountAmount($discount_amt);
            $item->setOriginalDiscountAmount($discount_amt);
            $item->setBaseOriginalDiscountAmount($discount_amt);
          

           
        }
        else
        {
            
            $item->setDiscountAmount($discountData->getAmount());
            $item->setBaseDiscountAmount($discountData->getBaseAmount());
            $item->setOriginalDiscountAmount($discountData->getOriginalAmount());
            $item->setBaseOriginalDiscountAmount($discountData->getBaseOriginalAmount());
        }
       

        return $this;
    }

   
}
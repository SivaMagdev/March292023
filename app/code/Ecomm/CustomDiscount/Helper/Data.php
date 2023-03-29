<?php

namespace Ecomm\CustomDiscount\Helper;

use Psr\Log\LoggerInterface;
use Magento\SalesRule\Model\Utility;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    protected $_session;

    protected $_serialize;

    protected $_ruleCollection;

    protected $_customerSegment;

    protected $_ruleFactory;

    protected $_checkoutSession;

    public $bestrule;

    protected $_orderCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Utility
     */
    protected $validatorUtility;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Serialize\SerializerInterface $serialize,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollection,
        \Magento\CustomerSegment\Model\Segment $customerSegment,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        LoggerInterface $logger,
        Utility $validatorUtility
    ) {
        $this->_session = $customerSession;
        $this->httpContext = $httpContext;
        $this->_orderFactory = $orderFactory;
        $this->_serialize = $serialize;
        $this->_ruleCollection = $ruleCollection;
        $this->_customerSegment = $customerSegment;
        $this->_ruleFactory = $ruleFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->logger = $logger;
        $this->validatorUtility = $validatorUtility;

        parent::__construct($context);
    }

    public function getCurrentQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    public function getTotalOrders($quote)
    {

        /*$customerId = $this->_session->getCustomer()->getId();
        
        $this->orders = $this->_orderFactory->create()->getCollection()->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'customer_id',
            $customerId
        )->setOrder(
            'created_at',
            'desc'
        );
        return count($this->orders);*/

        $customerId = $quote->getCustomer()->getId();
        $totalOrders = $this->_orderCollectionFactory->create()->addFieldToSelect('*')->addFieldToFilter('customer_id',$customerId)->setOrder('created_at','desc');

        $totalOrderCount = $totalOrders->count();

        return $totalOrderCount;
    }

    public function getAllRules($couponcode)
    {

        $rules = $this->_ruleCollection->create();


        if(empty($couponcode))
            $rules->addFieldToFilter("is_active",['eq' => 1])->addFieldToFilter("code",['null' => true])->load();
        else
            $rules->addFieldToFilter("is_active",['eq' => 1])->addFieldToFilter("code",['eq' => $couponcode])->load();
          

        return $rules;
    }


    public function unserializeData($serializeddata)
    {
        return $this->_serialize->unserialize($serializeddata);
    }


    //To create the cart price rules conditions
    public function getConditions($subtotal,$shoppingcartrules,$totalitems)
    {
      
        
        if($shoppingcartrules['simple_action'] == 'by_percent')
            $condrule[$shoppingcartrules['rule_id']]=$subtotal  * $shoppingcartrules['discount_amount']/100;
        else if($shoppingcartrules['simple_action'] == 'cart_fixed')
            $condrule[$shoppingcartrules['rule_id']]= $shoppingcartrules['discount_amount'];

        else if($shoppingcartrules['simple_action'] == 'by_fixed')
            $condrule[$shoppingcartrules['rule_id']]= $totalitems * $shoppingcartrules['discount_amount'];
         
    

        return $condrule;
    }


    //prepare customer segment conditions
    public function getCustomerConditions($totalOrders,$cond)
    {

        $condrule = [];

        $condstr = $totalOrders.$cond['operator'].$cond['value'].' && ';
        
        $condition = eval("return ".substr($condstr, 0, -4).";");        
    
        return $condition;
    }





    //To check whether the conditions are for customer segment or for normal users

    public function checkConditions($totalorders,$rule_id)
    {
        $returnval = [];

        $tempCustomerRules = [];
        $rule = $this->_ruleFactory->create()->load($rule_id);

        $ruledata = $rule->getData();
        $allconditions = $this->unserializeData($ruledata['conditions_serialized']);


        //$this->logger->info('discountHelper:'.$ruledata['uses_per_customer']);

       
        if(!empty($allconditions['conditions'])) {
            foreach($allconditions['conditions'] as $cond) {
                
                //Checking for Customer Segment
                $type = array('Magento\CustomerSegment\Model\Segment\Condition\Segment');
                if(in_array($cond['type'],$type))
                {
                    $segmentvalue = $this->_customerSegment->load($cond['value']);
                    $segmentData = $segmentvalue->getData();
                    if(!empty($segmentData['conditions_serialized']))
                    {
                        $conditions_serialized = $this->unserializeData($segmentData['conditions_serialized']);
                        $condition_type = array('Magento\CustomerSegment\Model\Segment\Condition\Sales\Ordersnumber');
                       
                        foreach($conditions_serialized['conditions'] as $condval)
                        {
                        
                            if(in_array($condval['type'],$condition_type))
                            {
                                $tempCustomerRules[] = $rule_id;
                                $res = $this->getCustomerConditions($totalorders,$condval);
                                if($res)
                                    $returnval['customer'] = $rule_id;
                                
                            }
                        }
                    }
                }
                else
                {
                    if (!in_array($rule_id, $tempCustomerRules)) {
                        $returnval['others'] = $rule_id;
                    }
                    
                }
            }
        }
        else
        {
            if (!in_array($rule_id, $tempCustomerRules)) {
                $returnval['others'] = $rule_id;
            }
        }


       
        return $returnval;

    }


    /*public function getBestRule($subtotal,$totalitems,$rule_ids,$flag='')
    {

        $condrule = [];
        
        if(!empty($rule_ids))
        {
            foreach($rule_ids as $rule_id){

                $condstr = '';
                $rule = $this->_ruleFactory->create()->load($rule_id);
                $ruledata = $rule->getData();
                $allconditions = $this->unserializeData($ruledata['conditions_serialized']);
                if(!empty($allconditions['conditions']))
                {
                    foreach($allconditions['conditions'] as $cond)
                    {

                        $type = array('Magento\CustomerSegment\Model\Segment\Condition\Segment');

                        if(!in_array($cond['type'],$type))
                        {
                            
                            $condstr.= $subtotal.$cond['operator'].$cond['value'].' && ';

                            $condition = eval("return ".substr($condstr, 0, -4).";");

                            if($condition)
                            {
                                if($ruledata['simple_action'] == 'by_percent')
                                    $condrule[$ruledata['rule_id']]=$subtotal  * $ruledata['discount_amount']/100;
                                else if($ruledata['simple_action'] == 'cart_fixed')
                                    $condrule[$ruledata['rule_id']]= $ruledata['discount_amount'];                        
                                else if($ruledata['simple_action'] == 'by_fixed')
                                    $condrule[$ruledata['rule_id']]= $totalitems * $ruledata['discount_amount'];
                        
                            }
                        }
                        else
                        {
                            if($flag == 'customer'){
                                if($ruledata['simple_action'] == 'by_percent')
                                    $condrule[$ruledata['rule_id']]=$subtotal  * $ruledata['discount_amount']/100;
                                else if($ruledata['simple_action'] == 'cart_fixed')
                                    $condrule[$ruledata['rule_id']]= $ruledata['discount_amount'];                        
                                else if($ruledata['simple_action'] == 'by_fixed')
                                    $condrule[$ruledata['rule_id']]= $totalitems * $ruledata['discount_amount'];
                            }
                        }
                        
                       
                       
                    }
                }
            }

            
            if(!empty($condrule))
            {

               
                $maxs = array_keys($condrule, max($condrule));
                $maxkey= $maxs[0];    
    
                return $maxkey;
            }
           
        }


       
       
        
    }*/


    public function getBestRule($item,$address,$rule_ids,$couponCode)
    {

        $returnval = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $condrule = $shipping_rule = [];

        $subtotal = $item->getQuote()->getSubtotal();
        $totalitems = $item->getTotalQty();
        
        $shipping_amount =  $address->getShippingAmount();
        $items = $item->getQuote()->getAllItems();

        //$couponCode = $item->getQuote()->getCouponCode();

        //echo $couponCode;

       
        if(!empty($rule_ids) && $subtotal > 0)
        {

         
            $rules = $this->_ruleCollection->create();

            if(empty($couponCode))
                $rules->addFieldToFilter("rule_id",['in' => $rule_ids]);
            else
                $rules->addFieldToFilter("code",['eq' => $couponCode]);
           

            
            //$rule = $this->_ruleFactory->create()->load($rule_id);
            //$ruledata = $rule->getData();
            
            
            foreach($rules as $rule)
            {
                //echo $rule->getId();
                $condstr = '';
                $ruledata = $rule->getData();

                $free_shipping = 0;
                
                $shipping_flag = [];

                
                //Checking whether the free shipping is applicable
                if($ruledata['simple_free_shipping']>0)
                {

                   
                    foreach($items as $itemval){
                        $product_id = $itemval->getProductId();
                        $item = $objectManager->get('Magento\Catalog\Model\Product')->load($product_id);
                        $product = $objectManager->create('Magento\Catalog\Model\Product');
                        $item->setProduct($product);                       
                        $avalidate = $rule->getActions()->validate($item);    
                        
                        if($avalidate)
                            $shipping_flag[]=1;
                        else
                            $shipping_flag[] = 0;
                    }


                    if(count(array_keys($shipping_flag, '1')) == count($shipping_flag))
                        $free_shipping=1;
                }

               
                //Checking conditions
                if($free_shipping)
                {
                    $shipping_rule[$ruledata['rule_id']]=$free_shipping;
                    $condrule[$ruledata['rule_id']]=$shipping_amount;
                }
                else
                {
                    

                    $allconditions = $this->unserializeData($ruledata['conditions_serialized']);
            
                    if(!empty($allconditions['conditions']))
                    {
                        foreach($allconditions['conditions'] as $cond)
                        {
    
                            $type = array('Magento\CustomerSegment\Model\Segment\Condition\Segment');
    
                            if(!in_array($cond['type'],$type))
                            {
                                $condstr.= $subtotal.$cond['operator'].$cond['value'].' && ';
    
                                $condition = eval("return ".substr($condstr, 0, -4).";");
    
                                if($condition)
                                {
                                    if($ruledata['simple_action'] == 'by_percent')
                                        $condrule[$ruledata['rule_id']]=$subtotal  * $ruledata['discount_amount']/100;
                                    else if($ruledata['simple_action'] == 'cart_fixed')
                                        $condrule[$ruledata['rule_id']]= $ruledata['discount_amount'];                        
                                    else if($ruledata['simple_action'] == 'by_fixed')
                                        $condrule[$ruledata['rule_id']]= $totalitems * $ruledata['discount_amount'];
                            
                                }
                            }
                            else
                            {
                                
                                    if($ruledata['simple_action'] == 'by_percent')
                                        $condrule[$ruledata['rule_id']]=$subtotal  * $ruledata['discount_amount']/100;
                                    else if($ruledata['simple_action'] == 'cart_fixed')
                                        $condrule[$ruledata['rule_id']]= $ruledata['discount_amount'];                        
                                    else if($ruledata['simple_action'] == 'by_fixed')
                                        $condrule[$ruledata['rule_id']]= $totalitems * $ruledata['discount_amount'];
                                
                            }
                            
                           
                           
                        }
                    }
                    else
                    {
                        if($ruledata['simple_action'] == 'by_percent')
                            $condrule[$ruledata['rule_id']]=$subtotal  * $ruledata['discount_amount']/100;
                        else if($ruledata['simple_action'] == 'cart_fixed')
                            $condrule[$ruledata['rule_id']]= $ruledata['discount_amount'];                        
                        else if($ruledata['simple_action'] == 'by_fixed')
                            $condrule[$ruledata['rule_id']]= $totalitems * $ruledata['discount_amount'];
                    }
                }
                
               
            }

           //print_r($condrule);
            
            if(!empty($condrule))
            {

               
                $maxs = array_keys($condrule, max($condrule));
                $maxkey= $maxs[0];   
                
                $returnval['rule_id'] = $maxkey;
                $returnval['rule_amount'] = $condrule[$maxkey];
                $returnval['free_shipping'] = (isset($shipping_rule[$maxkey]))?1:0;
                
    
               
            }
           
        }
     
        $this->bestrule = "sdfsdf";// $returnval;
        return $returnval;
        
    }


    public function getRulesByCondition($flag = '')
    {
     
        $rules = $this->_ruleCollection->create();

        if(empty($flag))
            $rules->addFieldToFilter("rule_id", ["eq"=> ""]);
        else
            $rules->addFieldToFilter("rule_id",['eq' => $flag]);


        return $rules;
    }


    public function getBestRuleDetails($quote,$total,$address)
    {
       
        $subtotal = $quote->getBaseSubtotal();
        $couponCode = $quote->getCouponCode();
        
        $final_rules_arr = [];
        //$totalOrderCount = $this->getTotalOrders(); 

        $customerId = $quote->getCustomer()->getId();
        $totalOrders = $this->_orderCollectionFactory->create()->addFieldToSelect('*')->addFieldToFilter('customer_id',$customerId)->setOrder('created_at','desc');

        $totalOrderCount = $totalOrders->count();

        $res = $customer_rules = $other_rules = [];


        //if(empty($couponCode)){
            if($subtotal > 0) {
                //getting all the active rules that are applicable customer segment wise or for others
                $rules = $this->getAllRules($couponCode);
                foreach ($rules as $ruleval) {
                    if (!$this->validatorUtility->canProcessRule($ruleval, $address)) {
                        continue;
                    }
                    $result = $this->checkConditions($totalOrderCount,$ruleval->getId());
                    if (!empty($result)) {
                        $res[$ruleval->getId()] = $this->checkConditions($totalOrderCount,$ruleval->getId());
                    }
                }

                //echo '<pre>'.print_r($res, true).'</pre>';

                //creating different arrays to seggregate the customer segment rules and the other rules
                if (!empty($res)) {
                    foreach ($res as $rulekey => $ruleval) {
                        if(isset($ruleval['others']) && !isset($ruleval['customer'])) {
                            $other_rules[] = $ruleval['others'];
                        }
                        if(isset($ruleval['customer'])){
                            $customer_rules[] = $ruleval['customer'];
                        }
                    }
                }

                $all_rules = array_merge($other_rules,$customer_rules);
    
                $final_rules=[];


                //Finding the Best Rule
    
                if(!empty($all_rules))
                {
                    
                    foreach($quote->getAllItems() as $item)
                    {
                        $final_rules_arr = $this->getBestRule($item,$address,$all_rules,$couponCode);  
                      //  print_r($final_rules_arr);
                    }
                }
            
            }
           
       /* }
        else
        {

        }*/

        return $final_rules_arr;
    }


    public function getItemDiscounts($item,$rule)
    {   
        $condrule = [];
       
        if($rule->getSimpleAction() == 'by_percent')
            $condrule['discount_amount']=($item->getPrice()*$item->getQty())  * $rule->getDiscountAmount()/100;
        else if($rule->getSimpleAction() == 'cart_fixed')
            $condrule['discount_amount']= ($item->getPrice()*$item->getQty()) - ($rule->getDiscountAmount()/$item->getQty());                        
        else if($rule->getSimpleAction() == 'by_fixed')
            $condrule['discount_amount']= ($item->getPrice()*$item->getQty()) - ($rule->getDiscountAmount()/$item->getQty());     

         
        return $condrule;
    }
}
<?php 
namespace Ecomm\Restriction\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Message\ManagerInterface; 
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product; 
use Magento\Checkout\Model\Session as CheckoutSession;
class ValidateaddCartObserver implements ObserverInterface 
{ 
        protected $cart;
        protected $messageManager; 
         protected $redirect; 
         protected $request; 
         protected $product;
          protected $customerSession;
           private $checkoutSession;
public function __construct( 
        RedirectInterface $redirect, 
        Cart $cart, 
        ManagerInterface $messageManager, 
        RequestInterface $request,
        Product $product, 
       \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory, 
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollection,
          CheckoutSession $checkoutSession
) 
{
        $this->redirect = $redirect;
        $this->cart = $cart;
        $this->request = $request;
         $this->_productloader = $_productloader;
        $this->product = $product;
        $this->customerSession = $customerSession;
         $this->_customerFactory = $customerFactory;
         $this->_orderCollectionFactory = $orderCollectionFactory;
         $this->messageManager = $messageManager;
          $this->checkoutSession = $checkoutSession;
        $this->addressCollection = $addressCollection;
  }
    public function execute(\Magento\Framework\Event\Observer $observer)
  {
        $quote = $this->cart->getQuote()->getItems();  
        $customerId=$this->customerSession->getCustomer()->getId();
        $customer =$this->_customerFactory->create()->load($customerId);
        $shippingAddressId = $customer->getDefaultShipping();

        $postValues = $this->request->getPostValue('product');             
        $items =$postValues;    
        $productData=$this->_productloader->create()->load($items);
        $productlimit=$productData->getProductLimits();
        $productenbaled=$productData->getYes();
        $productperiods=$productData->getPeriods();
        $productperiodswithdate=$productData->getAttributeText('periods');
        $productperiodsDay=$productData->getTotalDay();


        if ($productperiodswithdate=='Months') {
            $totaldays=-30*$productperiodsDay;
          
            $date=$totaldays.' day';
          
        }
     elseif ($productperiodswithdate=='Weeks') {
               $totaldays=-7*$productperiodsDay;
               $date=$totaldays.' day';  
     }
     else {
            $totaldays=-1;
               $date=$totaldays.' day';  
 
          }
            $to = date("Y-m-d h:i:s");
            $froms = strtotime($date, strtotime($to));
            $from = date('Y-m-d h:i:s', $froms); 

         if (!empty($items) && $productenbaled=='1') {

        $order = $this->_orderCollectionFactory->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'customer_id',
             $customerId  
        )->join(
                'sales_order_address',
                'sales_order_address.parent_id=main_table.entity_id',
                'sales_order_address.customer_address_id ')
            ->addFieldToFilter('customer_address_id', 
                array('customer_address_id' => $shippingAddressId)
            )
            ->join(
                'sales_order_item',
                'sales_order_item.order_id=main_table.entity_id',
                'sales_order_item.product_id ')
            ->addFieldToFilter('product_id', 
                array('product_id' => $items))
             ->addFieldToFilter('sales_order_item.created_at', array('from'=>$from, 'to'=>$to));
            $total=$order->getData();
             $qty = array();
            foreach ($total as $itemdata) {
                 $qty[]=$itemdata['total_qty_ordered'];

            }
            $dataid=array_sum($qty)/2;  
           $controller = $observer->getControllerAction();
            if ($dataid >= $productlimit) {
               $observer->getRequest()->setParam('product', false);
                $this->messageManager->addErrorMessage(__('This Product Already Limit has been corssed You can choose other shipping Address'));
                // $this->redirect->redirect($controller->getResponse(), 'checkout/cart');

        }  
        else{
                    $allItems = $this->getQouteId()->getItems();
 
foreach ($allItems as $item) {

     $qty=$item->getQty();
     $newqty=$qty++;                  
        $items =$postValues;    
        $productData=$this->_productloader->create()->load($items);
        $productlimit=$productData->getProductLimits();
        $productenbaled=$productData->getYes();
        $productperiods=$productData->getPeriods();
        $productperiodswithdate=$productData->getAttributeText('periods');
        $productperiodsDay=$productData->getTotalDay();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/customs.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Week/Month Id Harendra'); 
        $logger->info($productperiodswithdate);
        $logger->info($productenbaled);
        $logger->info($productperiodsDay);

        if ($productperiodswithdate=='Months') {
            $totaldays=-30*$productperiodsDay;
          
            $date=$totaldays.' day';
          
        }
     elseif ($productperiodswithdate=='Weeks') {
               $totaldays=-7*$productperiodsDay;
               $date=$totaldays.' day';  
     }
     else {
            $totaldays=-1;
               $date=$totaldays.' day';  
 
          }
            $to = date("Y-m-d h:i:s");
            $froms = strtotime($date, strtotime($to));
            $from = date('Y-m-d h:i:s', $froms); 
       if ($productenbaled=='1') {
        $order = $this->_orderCollectionFactory->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'customer_id',
             $customerId  
        )->join(
                'sales_order_address',
                'sales_order_address.parent_id=main_table.entity_id',
                'sales_order_address.customer_address_id ')
            ->addFieldToFilter('customer_address_id', 
                array('customer_address_id' => $shippingAddressId)
            )
            ->join(
                'sales_order_item',
                'sales_order_item.order_id=main_table.entity_id',
                'sales_order_item.product_id ')
            ->addFieldToFilter('product_id', 
                array('product_id' => $items))
             ->addFieldToFilter('sales_order_item.created_at', array('from'=>$from, 'to'=>$to));
            $total=$order->getData();
             $qty = array();
            foreach ($total as $itemdata) {
                 $qty[]=$itemdata['total_qty_ordered'];

            }
            $dataid=array_sum($qty)/2;  
            $totallimit=$newqty+$dataid;

           $controller = $observer->getControllerAction();
            if ($totallimit >= $productlimit) {
               $observer->getRequest()->setParam('product', false);
                $this->messageManager->addErrorMessage(__('This Product Already Limit has been corssed You can choose other shipping Address'));


                 //$this->redirect->redirect($controller->getResponse(), 'checkout/cart');

        }  
}
}
        }
}

}
  public function getQouteId()
    {
          return $this->checkoutSession->getQuote();
    }

}

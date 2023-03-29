<?php
namespace Ecomm\Restriction\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;

class ValidateCartObserver implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var Cart
     */
      protected $cart;
      protected $_orderCollectionFactory;
      protected $orders;
      protected $_customerFactory;

    /**
     * @param ManagerInterface $messageManager
     * @param RedirectInterface $redirect
     * @param CustomerCart $cart
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
         \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory, 
         \Magento\Customer\Model\Session $customerSession,
          \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollection,
          CustomerCart $cart
    ) {
         $this->_customerFactory = $customerFactory;
         $this->_orderCollectionFactory = $orderCollectionFactory;
         $this->messageManager = $messageManager;
        $this->addressCollection = $addressCollection;
         $this->customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->_productloader = $_productloader;
        $this->cart = $cart;
    }

    /**
     * Validate Cart Before going to checkout
     * - event: controller_action_predispatch_checkout_index_index
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {

        $quote = $this->cart->getQuote();
        $itemsCollection = $this->cart->getQuote()->getItemsCollection();
        $customerId=$this->customerSession->getCustomer()->getId();
        $customer =$this->_customerFactory->create()->load($customerId);
        $shippingAddressId = $customer->getDefaultShipping();
        $items = array();
       foreach ($itemsCollection as $item){
         $items =$item->getProductId();  
         $productData=$this->_productloader->create()->load($items); 
          $productlimit=$productData->getProductLimits();
         $productenbaled=$productData->getProductRestriction();
         $productsku=$productData->getSku();
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
        if ($productenbaled=='1')  {
        $this->orders = $this->_orderCollectionFactory->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'customer_id',
            $customerId  
        )->setOrder(
            'created_at',
            'desc'
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

            $total=$this->orders->getData();
             $qty = array();
            foreach ($total as $itemdata) {
                 $qty[]=$itemdata['total_qty_ordered'];

            }

            $dataid=array_sum($qty)/2+$item->getQty();   
            $controller = $observer->getControllerAction();
            $itemscheck = array();
            if ($dataid > $productlimit) {
             $avilablequote= $productlimit-array_sum($qty)/2;
            $this->messageManager->addNoticeMessage(
                __('Please review your cart! You added more than the limit.')
            );
            $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
    }
  
   
    }

    }



          
        }
    }

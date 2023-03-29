<?php
namespace Ecomm\HidePrice\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Ecomm\Theme\Helper\Output as ThemeHelper;

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

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var GetSalableQuantityDataBySku
     */
    private $stockRegistryBySku;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    protected $_date;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $modelProduct;

    private $modelProductOption;

    private $themeHelper;


    /**
     * @param ManagerInterface $messageManager
     * @param RedirectInterface $redirect
     * @param CustomerCart $cart
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        CustomerCart $cart,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        GetSalableQuantityDataBySku $stockRegistryBySku,
        \Magento\Catalog\Model\Product $modelProduct,
        \Magento\Catalog\Model\Product\Option $modelProductOption,
        CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        ThemeHelper $themeHelper,
        LoggerInterface $logger
    ) {
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->stockRegistryBySku = $stockRegistryBySku;
        $this->modelProduct = $modelProduct;
        $this->modelProductOption = $modelProductOption;
        $this->quoteRepository = $quoteRepository;
        $this->_date                = $date;
        $this->themeHelper          = $themeHelper;
        $this->logger = $logger;
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
        $controller = $observer->getControllerAction();
        $cartItemsQty = $quote->getItemsQty();
        $cartItems = $quote->getItems();

        $quoteId = $quote->getId();

        $error = [];
        $priceuskus = [];
        $inventryskus = [];

        $msgId = 1;

        $displaypriceupdatemessage = false;
        $displayinventorymessage = false;

        if($cartItems){
            foreach($cartItems as $cartItem){

                //$this->logger->info('Item Id: '.$cartItem->getId());
                $item = $quote->getItemById($cartItem->getId());

                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $customOptions = [];
                if(isset($options['options'])){
                    $customOptions = $options['options'];
                    //$this->logger->info('Item Options:'.print_r($customOptions, true));
                }

                $product = $this->modelProduct->load($item->getProductId());

                if (!empty($customOptions)) {

                    $stockQty = 0;
                    $option_value = 0;
                    $custom_option_price = 0;
                    $custom_option_title = '';
                    //$this->_logger->info('Item Options:'.print_r($customOptions, true));
                    //$this->_logger->info('Item Options:'.json_encode($customOptions));

                    foreach($customOptions as $customOption){
                        $option_value = $customOption['option_value'];
                    }

                    $delete_this_product = false;

                    $customOptions2 = $this->modelProductOption->getProductOptionCollection($product);
                    foreach($customOptions2 as $option) {
                        $values = $option->getValues();
                        //loop all child options
                        foreach($values as $value) {
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

                                $custom_option_price = $value->getDefaultPrice();
                                $custom_option_title = $value->getTitle();

                                if($custom_option_price <= 0)
                                    $delete_this_product = true;

                                //$this->logger->info('Option Qty:'.$value->getQuantity());
                                //$this->_logger->info('Option Price :'.$custom_option_price);

                            }
                            //$this->_logger->info('Item Options-2:'.json_encode($value->getData()));
                        }
                    }

                    if($delete_this_product == true) {
                        $item->delete();//deletes the item
                        $error[] = $item->getSku();
                        $msgId = 2;

                    } else {
                        if($custom_option_price != $cartItem->getPrice() && $delete_this_product == false) {
                            $displaypriceupdatemessage = true;

                            if($custom_option_title){
                                $priceuskus[] = $item->getSku().'('.$custom_option_title.')';
                            }

                            $customprice = $this->checkoutSession->getQuote()->getItemById($item->getId());
                            $customprice->setPrice($custom_option_price);
                            $customprice->setBasePrice($custom_option_price);
                            $customprice->setCustomPrice($custom_option_price);
                            $customprice->setOriginalCustomPrice($custom_option_price);
                            $customprice->getProduct()->setIsSuperMode(true);
                            $customprice->setBaseRowTotal($custom_option_price*$cartItem->getQty());
                            $customprice->setRowTotal($custom_option_price*$cartItem->getQty());
                            $customprice->save();
                        }

                        //$this->logger->info('Item Qty: '.$stockQty.' - Ordered Qty: '.$cartItem->getQty());

                        if ($stockQty < $cartItem->getQty()) {

                            if($custom_option_title) {
                                $inventryskus[] = $item->getSku().'('.$custom_option_title.')';
                            }/* else {
                                $inventryskus[] = $item->getSku().'(Shortdated)';
                            }*/

                            $displayinventorymessage = true;
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
                    $customerGroupId = $this->customerSession->getCustomer()->getGroupId();

                    if(isset($group_prices[$customerGroupId]) && $group_prices[$customerGroupId] > 0){
                        $group_price = $group_prices[$customerGroupId];
                    }
                    if($group_price > 0){
                        $customprice = $this->checkoutSession->getQuote()->getItemById($item->getId());
                        $customprice->setPrice($group_price);
                        $customprice->setBasePrice($group_price);
                        $customprice->setCustomPrice($group_price);
                        $customprice->setOriginalCustomPrice($group_price);
                        $customprice->getProduct()->setIsSuperMode(true);
                        $customprice->setBaseRowTotal($group_price*$cartItem->getQty());
                        $customprice->setRowTotal($group_price*$cartItem->getQty());
                        $customprice->save();

                    } else {

                        if(($item->getCustomPrice() > 0) || ($item->getPrice() <= 0)){
                            //$this->logger->info('vco Item Id: ' . $item->getId() . 'deleted');

                            $item->delete();//deletes the item

                            $error[] = $item->getSku();
                            $msgId = 2;

                        }
                    }

                    $salable_quantities = $this->stockRegistryBySku->execute($item->getSku());
                     $_productStock = $this->themeHelper->getStockItem($item->getProductId());

                    $stockQty = 0;

                    if((isset($salable_quantities[0]['qty']) && $salable_quantities[0]['qty'] > 0) && $_productStock->getQty() > 0){

                        $stockQty = $salable_quantities[0]['qty'];

                    }

                    //$this->logger->info('checkout index Item Qty: '.$stockQty.' - Ordered Qty: '.$cartItem->getQty());

                    if ($stockQty < $cartItem->getQty()) {
                        $error[] = $item->getSku();
                        $inventryskus[] = $item->getSku();
                        $displayinventorymessage = true;
                    }
                }
            }

        }

        if ($quoteId) {
            $quoteRepository = $this->quoteRepository->get($quoteId);
            $this->quoteRepository->save($quoteRepository->collectTotals());
        }

        $this->logger->info('checkout index inventryskus: '.implode(', ', $inventryskus));

        if($priceuskus){
            if($displaypriceupdatemessage) {
                $this->messageManager->addNoticeMessage(
                    __('Price has changed for the following items: '.implode(', ', $priceuskus)).'. Please review before placing order.'
                );
                $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
            }
        }
        if($inventryskus){

            if($displayinventorymessage) {
                $this->messageManager->addErrorMessage(
                    __('Following items exceed available quantity: '.implode(', ', $inventryskus).'. Please update/remove the items')
                );
                $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
            }
        }
    }
}
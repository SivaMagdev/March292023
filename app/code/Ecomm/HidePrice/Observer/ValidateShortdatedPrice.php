<?php

namespace Ecomm\HidePrice\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class ValidateShortdatedPrice implements ObserverInterface {

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Product
     */
    protected $_product; 

    /**
     * @var option
     */
    protected $_option;

    protected $quote;

    protected $quoteItem;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Address
     */
    protected $address;

    protected $cartHelper;

    public function __construct(
        Session $customerSession,
        Customer $customer,
        Cart $cart,
        CheckoutSession $checkoutSession,
        Product $_product,
        Option $_option,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        CartRepositoryInterface $quoteRepository,
        Address $address,
        \Magento\Checkout\Helper\Cart $cartHelper
    ) {
        $this->customer = $customer;
        $this->customerSession = $customerSession;
        $this->cart = $cart;
        $this->address = $address;
        $this->checkoutSession = $checkoutSession;
        $this->_product = $_product;
        $this->_option = $_option;
        $this->quoteRepository = $quoteRepository;
        $this->quote = $quote;
        $this->quoteItem = $quoteItem;
        $this->cartHelper = $cartHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        $items = $this->cartHelper->getCart()->getItems();

        $quoteId = 0;

        //echo "<pre>".print_r($items, true).'</pre>';
        if($items){
            foreach($items as $item){

                //echo $item->getId();
                //echo $item->getQuoteId();

                $quoteId = $item->getQuoteId();

                //echo $item->getProduct()->getId();
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $customOptions = [];
                if(isset($options['options'])){
                    $customOptions = $options['options'];
                }

                //echo "<pre>".print_r($customOptions, true).'</pre>';

                if (!empty($customOptions)) {
                    $option_value = 0;
                    $custom_option_price = 0;
                    //$this->_logger->info('Item Options:'.print_r($customOptions, true));
                    //$this->_logger->info('Item Options:'.json_encode($customOptions));

                    foreach($customOptions as $customOption){
                        $option_value = $customOption['option_value'];
                    }

                    $product = $this->_product->load($item->getProduct()->getId());
                    $customOptions2 = $this->_option->getProductOptionCollection($product);
                    foreach($customOptions2 as $option) {
                        $values = $option->getValues();
                        //loop all child options
                        foreach($values as $value) {
                            if($option_value == $value->getOptionTypeId()) {
                                $custom_option_price = $value->getDefaultPrice();

                            }
                        }
                    }

                    $customprice = $this->checkoutSession->getQuote()->getItemById($item->getId());
                    $customprice->setCustomPrice($custom_option_price);
                    $customprice->setOriginalCustomPrice($custom_option_price);
                    $customprice->save();
                    $quoteId = $item->getQuoteId();

                    //echo 'custom_option_price'.$custom_option_price.'<br />';
                }

                if ($quoteId) {
                    $quote = $this->quoteRepository->get($quoteId);
                    $this->quoteRepository->save($quote->collectTotals());
                }

            }
        }

        //exit();

        /*$customer = $observer->getEvent()->getCustomer();
        $customerId = $customer->getId();
        // check session is active or not.

        echo $customerId;

        if ($customerId) {

            $itemId = 0;
            $qty = 0;
            $quoteId = 0;

            $sessionItems = $this->checkoutSession->getQuote()->getAllItems();

            foreach ($sessionItems as $session_item) {
                $qty = (int) $session_item['qty'];
                $productId = $session_item['product_id'];
                echo $productId.'-'.$qty.'<br />';
                $product = $this->_product->load($productId);
                $setcustomprice = $this->checkoutSession->getQuote()->getItemById($session_item['item_id']);
                $uom = $product->getUom();

                    if ($price > 0) {
                        $customprice = $this->checkoutSession->getQuote()->getItemById($session_item['item_id']);
                        $customprice->setCustomPrice($price);
                        $customprice->setOriginalCustomPrice($price);
                        $customprice->save();
                        $quoteId = $session_item['quote_id'];
                    }
                }
            }

            if ($quoteId) {
                $quote = $this->quoteRepository->get($quoteId);
                $this->quoteRepository->save($quote->collectTotals());
            }
        }

        exit();*/
    }

}
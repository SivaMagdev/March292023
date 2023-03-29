<?php

namespace Ecomm\HidePrice\Controller\Cart;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class Index extends \Magento\Checkout\Controller\Cart implements HttpGetActionInterface
{

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var GetSalableQuantityDataBySku
     */
    private $stockRegistryBySku;

    private $modelProduct;

    private $modelProductOption;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    protected $_date;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AttributeRepositoryInterface $eavAttributeRepository
     * @param RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AttributeRepositoryInterface $eavAttributeRepository,
        RedirectFactory $resultRedirectFactory,
        GetSalableQuantityDataBySku $stockRegistryBySku,
        \Magento\Catalog\Model\Product $modelProduct,
        \Magento\Catalog\Model\Product\Option $modelProductOption,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        LoggerInterface $logger
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->session = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->stockRegistryBySku = $stockRegistryBySku;
        $this->modelProduct = $modelProduct;
        $this->modelProductOption = $modelProductOption;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->_date                = $date;
        $this->_logger = $logger;
    }

    /**
     * Shopping cart display action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        /*$customerId = $this->session->getCustomerId();

        //echo $customerId;
        if($customerId) {

            $customer = $this->customerRepository->getById($customerId);

            //echo $customer->getResource()->getAttribute('application_status')->getFrontend()->getValue($customer);

            $attributes = $this->eavAttributeRepository->get(\Magento\Customer\Model\Customer::ENTITY, 'application_status');
            //$options = $attributes->getSource()->getAllOptions(false);

            //echo '<pre>'.print_r($options, true).'</pre>';

            $application_status = $attributes->getSource()->getOptionText($customer->getCustomAttribute("application_status")->getValue());

            if($application_status != 'Approved'){
                $this->messageManager->addErrorMessage('You cannot place an order as your account status is not approved. Please contact customer care to resolve the issue.');
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('/');
                return $resultRedirect;
            }
        }

        $ndcs = [];

        $isItemRemoved = false;

        $quote = $this->cart->getQuote();

        $msgId = 1;

        $cartItems = $quote->getItems();

        $quoteId = $quote->getId();
        //$this->_logger->info('quoteId:'.print_r($quoteId, true));

        if($cartItems){
            foreach($cartItems as $cartItem){

                //$this->_logger->info('Item Id: '.$cartItem->getId());
                $item = $quote->getItemById($cartItem->getId());

                //echo $item->getPrice().' - '.$item->getCustomPrice();

                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $customOptions = [];
                if(isset($options['options'])){
                    $customOptions = $options['options'];
                    //$this->_logger->info('Item Options:'.print_r($customOptions, true));
                }

                //echo '<pre>'.print_r($customOptions, true).'</pre>';

                if (!empty($customOptions)) {

                    $stockQty = 0;
                    $option_value = 0;
                    $custom_option_price = 0;
                    //$this->_logger->info('Item Options:'.print_r($customOptions, true));
                    //$this->_logger->info('Item Options:'.json_encode($customOptions));

                    foreach($customOptions as $customOption){
                        $option_value = $customOption['option_value'];
                    }

                    $delete_this_product = false;

                    $custom_option_ids = [];

                    $product = $this->modelProduct->load($item->getProductId());
                    $customOptions2 = $this->modelProductOption->getProductOptionCollection($product);
                    foreach($customOptions2 as $option) {
                        $values = $option->getValues();
                        //loop all child options
                        foreach($values as $value) {

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

                                $custom_option_price = $value->getDefaultPrice();

                                //$this->logger->info('Option Qty:'.$value->getQuantity());
                                //$this->_logger->info('Option Price :'.$custom_option_price);

                            }
                            //$this->_logger->info('Item Options-2:'.json_encode($value->getData()));
                        }
                    }

                    if($delete_this_product == true || !in_array($option_value, $custom_option_ids)) {

                        $ndcs[] = $item->getSku();

                        $item->delete();//deletes the item

                        $msgId = 2;

                    } else {
                        $customprice = $this->checkoutSession->getQuote()->getItemById($item->getId());
                        $customprice->setCustomPrice($custom_option_price);
                        $customprice->setOriginalCustomPrice($custom_option_price);
                        $customprice->save();

                        //$this->logger->info('Item Qty: '.$stockQty.' - Ordered Qty: '.$cartItem->getQty());

                        if ($stockQty < $cartItem->getQty()) {

                            $ndcs[] = $item->getSku().'(Shortdated)';
                        }
                    }

                } else {

                    if($item->getCustomPrice() > 0){
                        //echo 'deleted';

                        $ndcs[] = $item->getSku();

                        $item->delete();//deletes the item

                        $msgId = 2;

                    }

                    $salable_quantities = $this->stockRegistryBySku->execute($item->getSku());

                    $stockQty = 0;

                    if(isset($salable_quantities[0]['qty'])){

                        $stockQty = $salable_quantities[0]['qty'];

                    }

                    //$this->logger->info('Item Qty: '.$stockQty.' - Ordered Qty: '.$cartItem->getQty());

                    if ($stockQty < $cartItem->getQty()) {

                        $ndcs[] = $item->getSku();
                    }
                }

            }
        }

        if ($quoteId) {
            $quoteRepository = $this->quoteRepository->get($quoteId);
            $this->quoteRepository->save($quoteRepository->collectTotals());
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Shopping Cart'));


        if($ndcs){

            //echo 'msgId-'.$msgId;

            if($msgId == 2) {
                $this->messageManager->addNoticeMessage(
                    __('Price of few items in your cart has changed: '.implode(', ', $ndcs)).'. Please review the products & add to cart!.'
                );
            } else {
                //$this->messageManager->addNoticeMessage(
                    __('Please review your cart!, Few items in Cart do not have enough stock to fulfill your order: '.implode(', ', $ndcs))
                );
                $this->messageManager->addNoticeMessage(
                    __('Price of few items in your cart has changed: '.implode(', ', $ndcs)).'. Please review the products & add to cart!.'
                );
            }

        }

        return $resultPage;*/
    }

}
<?php

namespace Ecomm\HidePrice\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\Framework\App\Response\RedirectInterface;

class Checkstock implements ObserverInterface
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var GetSalableQuantityDataBySku
     */
    private $stockRegistryBySku;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var DataObjectFactory
     */
    private $objectFactory;

    private $_request;

    private $_quoteFactory;

    /**
     * @var RequestQuantityProcessor
     */
    private $quantityProcessor;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    private $productSalabilityErrorFactory;

    /**
    * @var ProductSalableResultInterfaceFactory
    */
    private $productSalableResultFactory;


    /**
     * Quote constructor.
     *
     * @param StockRegistryInterface $stockRegistry
     * @param ManagerInterface $messageManager
     * @param DataObjectFactory $objectFactory
     */

    public function __construct(
        StockRegistryInterface $stockRegistry,
        GetSalableQuantityDataBySku $stockRegistryBySku,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        RedirectInterface $redirect,
        DataObjectFactory $objectFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Checkout\Model\Cart\RequestQuantityProcessor $quantityProcessor,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Catalog\Model\Product $modelProduct,
        \Magento\Catalog\Model\Product\Option $modelProductOption,
        CheckoutSession $checkoutSession,
        Json $json,
        LoggerInterface $logger,
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductSalableResultInterfaceFactory $productSalableResultFactory
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->stockRegistryBySku = $stockRegistryBySku;
        $this->messageManager = $messageManager;
        $this->_eventManager = $eventManager;
        $this->redirect = $redirect;
        $this->objectFactory = $objectFactory;
        $this->_request = $request;
        $this->_quoteFactory = $quoteFactory;
        $this->quantityProcessor = $quantityProcessor;
        $this->modelProduct = $modelProduct;
        $this->modelProductOption = $modelProductOption;
        $this->checkoutSession = $checkoutSession;
        $this->json = $json;
        $this->logger = $logger;
        $this->productSalableResultFactory = $productSalableResultFactory;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {

        /*$controller = $observer->getControllerAction();

        $event = $observer->getEvent();

        //$infoDataObject = new \Magento\Framework\DataObject($observer);

        $infoDataObject = $observer->getEvent()->getInfo()->toArray();
        $this->logger->info('infoDataObject:'.print_r($infoDataObject, true));

        $cartData = $this->_request->getParam('cart');
        //$this->logger->info('infoDataObject:'.print_r($cartData, true));

        $cartData = $this->quantityProcessor->process($cartData);

        $quote = $this->checkoutSession->getQuote();

        //echo '<pre>cartData: '.print_r($cartData, true).'</pre>'; exit();
        foreach ($cartData as $itemId => $itemInfo) {
            $item = $quote->getItemById($itemId);
            //$itemoption = $quote->getItemOptionById($itemId);
            //$this->logger->info('Item SKU:'.json_encode($item->getSku(), true));
            //$this->logger->info('Item option:'.print_r($item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct()), true));
            //$this->logger->info('Item Options:'.print_r($itemoption->getData(), true));
            //echo '<pre>cartData: '.print_r($item->getProductId().''.$itemInfo['qty'], true).'</pre>';

            //echo $item->getSku().' - '.$itemInfo['qty'];

            $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            $customOptions = [];
            if(isset($options['options'])){
                $customOptions = $options['options'];
                //$this->logger->info('Item Options:'.print_r($customOptions, true));
            }
            if (!empty($customOptions)) {
                $stockQty = 0;
                $option_value = 0;
                $custom_option_price = 0;
                //$this->_logger->info('Item Options:'.print_r($customOptions, true));
                //$this->_logger->info('Item Options:'.json_encode($customOptions));

                foreach($customOptions as $customOption){
                    $option_value = $customOption['option_value'];
                }

                $product = $this->modelProduct->load($item->getProductId());
                $customOptions2 = $this->modelProductOption->getProductOptionCollection($product);
                foreach($customOptions2 as $option) {
                    $values = $option->getValues();
                    //loop all child options
                    foreach($values as $value) {
                        if($option_value == $value->getOptionTypeId()) {
                            $stockQty = $value->getQuantity();

                            //$this->logger->info('Option Qty:'.$value->getQuantity());

                        }
                        //$this->_logger->info('Item Options-2:'.json_encode($value->getData()));
                    }
                }
            } else {

                $salable_quantities = $this->stockRegistryBySku->execute($item->getSku());

                $stockQty = 0;

                if(isset($salable_quantities[0]['qty'])){

                    $stockQty = $salable_quantities[0]['qty'];

                }
            }

            $this->logger->info('Stock Qty:'.$stockQty.', Requested Qty: '.$itemInfo['qty']);

            //echo $item->getSku().' - '.$stockQty;

            if ($stockQty < $itemInfo['qty']) {

                /*$errors = [
                    $this->productSalabilityErrorFactory->create([
                        'code' => 'is_correct_qty-max_sale_qty',
                        'message' => 'The requested qty exceeds the maximum stock available'
                    ])
                ];
                //echo '<pre>cartData: '.print_r($errors, true).'</pre>';
                $this->productSalableResultFactory->create(['errors' => $errors]);
                //$this->messageManager->addErrorMessage("The requested qty exceeds the maximum stock available");

                $this->_eventManager->dispatch('checkout_cart_update_items_before',['cart' => $this, 'info' => $infoDataObject]);

                //$this->redirect->redirect($controller->getResponse(), 'checkout/cart');

                //$this->_eventManager->dispatch('checkout_cart_update_item_complete', ['cart' => $this, 'info' => $infoDataObject]);

                return $this;


            }

        }*/

        //$this->_eventManager->dispatch('checkout_cart_update_items_before');

        $data = $observer->getEvent()->getInfo()->toArray();

        //echo '<pre>'.print_r($data, true).'</pre>'; exit();

        $infoDataObject_ = new \Magento\Framework\DataObject($data);

        /*$infoDataObject = $this->objectManagerHelper->getObject(
            DataObject::class,
            ['data' => $data]
        );*/

        //echo '<pre>'.print_r($infoDataObject, true).'</pre>'; exit();

        $this->messageManager->addErrorMessage(__("We don't have some of the products you want."));
       /* $this->_eventManager->dispatch(
            'checkout_cart_update_items_before',
            ['cart' => $this, 'info' => $infoDataObject]
        );*/


        return $this;
    }

}
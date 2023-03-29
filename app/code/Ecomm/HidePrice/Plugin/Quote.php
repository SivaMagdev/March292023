<?php
namespace Ecomm\HidePrice\Plugin;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Ecomm\Theme\Helper\Output as ThemeHelper;
use Psr\Log\LoggerInterface;

class Quote
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
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $checkoutSession;

    private $modelProduct;

    private $modelProductOption;

    private $themeHelper;

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
        ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Product $modelProduct,
        \Magento\Catalog\Model\Product\Option $modelProductOption,
        ThemeHelper $themeHelper,
        LoggerInterface $logger,
        DataObjectFactory $objectFactory
    ) {
        $this->stockRegistry        = $stockRegistry;
        $this->stockRegistryBySku   = $stockRegistryBySku;
        $this->messageManager       = $messageManager;
        $this->checkoutSession      = $checkoutSession;
        $this->modelProduct         = $modelProduct;
        $this->modelProductOption   = $modelProductOption;
        $this->themeHelper          = $themeHelper;
        $this->logger               = $logger;
        $this->objectFactory        = $objectFactory;
    }

    public function beforeAddProduct(
        \Magento\Quote\Model\Quote $subject,
        \Magento\Catalog\Model\Product $product,
        $request
    ) {
        if ($request === null) {
            $request = 1;
        }
        if (is_numeric($request)) {
            $request = $this->objectFactory->create(['qty' => $request]);
        }

        if ($product->getId() && $request) {
            //$this->logger->info('subject:'.json_encode($subject->getData(), true));
            //$this->logger->info('Request:'.json_encode($request->getData(), true));
            $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
            $stockQty = $stockItem->getQty();

            //$this->logger->info('Product ID:'.$product->getId());

            $quote = $this->checkoutSession->getQuote();

            //$this->logger->info('Quote:'.json_encode($quote->getItems()->getData(), true));
            /*foreach($quote->getItems() as $item) {
                $cartItems[$item->getProductId()] = [
                    'product_id'=>$item->getProductId(),

                ];
                $this->logger->info('Quote:'.json_encode($item->getProductId(), true));
                //if($item->getProductId() == $product->getId()) {
                    //print_r($item->getData());
                    //$this->logger->info('Quote:'.json_encode($item->getData(), true));
                    $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                    $this->logger->info('Quote:'.json_encode($options, true));
                //}
            }*/
            //$cartItems = $quote->getItemByProduct($product);
            //$this->logger->info('Quote:'.json_encode($cartItems->getData(), true));
            $currentQtyInCart = 0;
            if($request->getoptions()){
                //$this->logger->info('Item Options:'.json_encode($request->getoptions(), true));
                $cartItems = [];
                foreach($quote->getItems() as $item) {
                    //$this->logger->info('getProductId:'.json_encode($item->getProductId(), true));
                    $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                    //$this->logger->info('Quote:'.json_encode($options, true));
                    $customOptions = [];
                    if(isset($options['options'])){
                        $customOptions = $options['options'];
                        //$this->logger->info('Item Options:'.print_r($customOptions, true));
                    }
                    if (!empty($customOptions)) {
                        $options_item_id = 0;
                        foreach($customOptions as $customOption){
                            $options_item_id = $customOption['option_value'];
                        }
                        $cartItems[$item->getProductId()][] = [$options_item_id=> $item->getQty()];
                    }
                }
                //$this->logger->info('cartItems with Options:'.print_r($cartItems, true));

                $option_value = '';
                foreach($request->getoptions() as $value){
                    $option_value = $value;
                }

                $product = $this->modelProduct->load($request->getProduct());
                $customOptions2 = $this->modelProductOption->getProductOptionCollection($product);
                foreach($customOptions2 as $option) {
                    $values = $option->getValues();
                    //loop all child options
                    foreach($values as $value) {
                        if($option_value == $value->getOptionTypeId()) {
                            $stockQty = $value->getQuantity();

                            //$this->logger->info('Option Qty:'.$value->getQuantity());

                        }
                        //$this->logger->info('Item Options-2:'.json_encode($value->getData()));
                    }
                }

                if(isset($cartItems[$request->getProduct()])){
                    //$this->logger->info('Cart Options:'.json_encode($cartItems[$request->getProduct()]));
                    foreach ($cartItems[$request->getProduct()] as $cartItemOptions) {
                        //$this->logger->info('Cart Options:'.$option_value.': '.print_r($cartItemOptions, true));
                        if(isset($cartItemOptions[$option_value])){

                            $currentQtyInCart = 0;

                            $stockQty -= $cartItemOptions[$option_value];
                            //$this->logger->info('Item Options:'.$cartItemOptions[$option_value]);

                        }
                    }
                }
            } else {
                $salable_quantities = $this->stockRegistryBySku->execute($product->getSku());
                if(isset($salable_quantities[0]['qty'])){
                    $stockQty = $salable_quantities[0]['qty'];
                }

                if($quote->getItemByProduct($product)){
                    $cartItem = $quote->getItemByProduct($product);
                    //$this->logger->info('Quote:'.json_encode($cartItem->getData(), true));
                    $currentQtyInCart = 0;
                    $stockQty -= $cartItem->getQty();
                }
            }

            //$this->logger->info('stockQty: '.$stockQty.' - requestQty: '.$request->getQty().' - currentQtyInCart: '.$currentQtyInCart);


            if ($stockQty < $request->getQty()) {
                if($stockQty > 0){
                    $remainingQty = $request->getQty() - $stockQty;
                    $request->setQty($stockQty);
                    $this->messageManager->addErrorMessage(__('At this time, you can add %1 qty to cart. To Order rest %2 qtys, please contact customer service/sales rep or raise a service ticket from our Help & Support Portal', $stockQty, $remainingQty));
                } else {
                    $requestQty = $request->getQty();
                    unset($request);
                    $this->messageManager->addErrorMessage(__('At this time, you can add %1 qty to cart. To Order rest %2 qtys, please contact customer service/sales rep or raise a service ticket from our Help & Support Portal', $stockQty, $requestQty));
                }
            }
        }

        return [$product, $request];
    }
}
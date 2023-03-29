<?php

namespace Ecomm\HidePrice\Observer;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;

class UpdateItemQty
{
	/**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    private $_request;

    private $modelProduct;

    private $modelProductOption;

    /**
     * @var RequestQuantityProcessor
     */
    private $quantityProcessor;

    /**
     * @var GetSalableQuantityDataBySku
     */
    private $stockRegistryBySku;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    protected $resultRedirectFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

	public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Checkout\Model\Cart\RequestQuantityProcessor $quantityProcessor,
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $stockRegistryBySku,
        CheckoutSession $checkoutSession,
        \Magento\Catalog\Model\Product $modelProduct,
        \Magento\Catalog\Model\Product\Option $modelProductOption,
        RedirectFactory $resultRedirectFactory,
        LoggerInterface $logger
    )
    {
        $this->messageManager = $messageManager;
        $this->_request = $request;
        $this->quantityProcessor = $quantityProcessor;
        $this->modelProduct = $modelProduct;
        $this->modelProductOption = $modelProductOption;
        $this->stockRegistryBySku = $stockRegistryBySku;
        $this->checkoutSession = $checkoutSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->logger = $logger;
    }

    public function beforeExecute(\Magento\Checkout\Controller\Cart\UpdatePost $subject)
	{

		$cartData = $this->_request->getParam('cart');

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

            //$this->logger->info('Stock Qty:'.$stockQty.', Requested Qty: '.$itemInfo['qty']);

            //echo $item->getSku().' - '.$stockQty;

            if ($stockQty < $itemInfo['qty']) {
                //$this->messageManager->addErrorMessage(__('The requested qty exceeds the maximum stock available.'));
                $this->_request->setParam('cart', []);
            }

        }

    }
}


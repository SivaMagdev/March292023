<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecomm\AutoUpdateCartAjax\Controller\Index;

use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;

/**
 * Autoupdatecart ajax request
 *
 * @package Mymodules\CustomCheckout\Controller\Index
 */
class Autoupdatecart extends Action 
{

    /**
     * @var RequestQuantityProcessor
     */
    private $quantityProcessor;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

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

    protected  $stockItemRepository;

    protected $quoteRepository;

    protected $quoteRepo;

    private $getSalableQuantityDataBySku;

    /**
     * UpdateItemQty constructor
     *
     * @param Context $context
     * @param RequestQuantityProcessor $quantityProcessor
     * @param FormKeyValidator $formKeyValidator
     * @param CheckoutSession $checkoutSession
     * @param Json $json
     * @param LoggerInterface $logger
     */

    public function __construct(
        Context $context,
        RequestQuantityProcessor $quantityProcessor,
        FormKeyValidator $formKeyValidator,
        CheckoutSession $checkoutSession,
        Json $json,
        LoggerInterface $logger,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionCollection,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteRepository $quoteRepo,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku
        ) {
        //$this->quantityProcessor = $quantityProcessor;
        $this->formKeyValidator = $formKeyValidator;
        $this->checkoutSession = $checkoutSession;
        $this->json = $json;
        $this->logger = $logger;
        $this->stockItemRepository = $stockItemRepository;
        $this->productRepository = $productRepository;
        $this->optionCollection = $optionCollection;
        $this->quantityProcessor = $quantityProcessor ?: $this->_objectManager->get(RequestQuantityProcessor::class);
        $this->quoteRepository = $quoteRepository;
        $this->quoteRepo = $quoteRepo;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        parent::__construct($context);
    }

    /**
     * Controller execute method
     *
     * @return void
     */
    public function execute()
    {
       
        try {
            $this->validateRequest();
            $this->validateFormKey();

            $cartData = $this->getRequest()->getParam('cart');

            $this->validateCartData($cartData);

            $cartData = $this->quantityProcessor->process($cartData);
           
            $quote = $this->checkoutSession->getQuote();

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
            $cartId=$cart->getQuote()->getId();

           
            foreach ($cartData as $itemId => $itemInfo) {
                $item = $quote->getItemById($itemId);
                $itemsku=$item->getSku();

                $qty = isset($itemInfo['qty']) ? (double) $itemInfo['qty'] : 0;

                             
                if(isset($itemInfo['option']))
                {
                    
                    $opt=$this->getProductCustomOption($itemsku);
                   
                    if(!empty($opt))
                    {
                        foreach($opt as $options){
                            if($options['title']==$itemInfo['option'])
                            {
                               
                                if($qty>$options['quantity'])
                                {
                                   
                                   $stockqty=$qty - $options['quantity'];

                                   $quoterep = $this->quoteRepo->get($cartId);
                                   $qitem = $quoterep->getItemById($itemId);
                                   if (!$qitem) {
                                    continue;
                                   }
                                   $qitem->setQty($options['quantity']);
                                   $qitem->save(); 


                                   $this->jsonResponse($itemId."~To Order rest ".$stockqty." qtys,please contact customer service sales rep or raise a service ticket from our Help & Support Portal");
                                }
                                else
                                {
                                    $quoterep = $this->quoteRepo->get($cartId);
                                    $qitem = $quoterep->getItemById($itemId);
                                    if (!$qitem) {
                                     continue;
                                    }
                                    $qitem->setQty($qty);
                                    $qitem->save(); 
                                }
                            }
                            
                    }
                    }
                }
                else
                {
                  
                    $itemqty=$item->getQty();
                    $saleableqty = $this->getSalableQuantityDataBySku->execute($item->getSku());
                   
                    //if($qty>$saleableqty)
                    if($qty>$saleableqty[0]['qty'])
                    {
                        
                        //$this->jsonResponse($itemId);
                        $stockqty= $qty - $saleableqty[0]['qty'];                         
                        
                        
                        $quoterep = $this->quoteRepo->get($cartId);
                        $qitem = $quoterep->getItemById($itemId);
                        if (!$qitem) {
                         continue;
                        }
                        $qitem->setQty($saleableqty[0]['qty']);
                        $qitem->save(); 
                        
                        //$this->jsonResponse($itemId);
                        $this->jsonResponse($itemId."~To Order rest ".$stockqty." qtys,please contact customer service sales rep or raise a service ticket from our Help & Support Portal");
                    }
                    else
                    {
                        $quoterep = $this->quoteRepo->get($cartId);
                        $qitem = $quoterep->getItemById($itemId);
                        if (!$qitem) {
                         continue;
                        }
                        $qitem->setQty($qty);
                        $qitem->save(); 
                    }
                }

            }
           

        } catch (LocalizedException $e) {
            $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->jsonResponse('Something went wrong while saving the page. Please refresh the page and try again.');
        }
    }


    private function getProductCustomOption($sku)
    {
        $product=[];
        try {
            try {
                $product = $this->productRepository->get($sku);
            } catch (\Exception $exception) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('Such product doesn\'t exist'));
            }
            $productOption = $this->optionCollection->create()->getProductOptions($product->getEntityId(),$product->getStoreId(),false);
            $optionData = [];
            foreach($productOption as $option) {
                $optionId = $option->getId();
                $optionValues = $product->getOptionById($optionId);
                if ($optionValues === null) {
                    throw \Magento\Framework\Exception\NoSuchEntityException::singleField('optionId', $optionId);
                }
                foreach($optionValues->getValues() as $values) {
                    $optionData[] = $values->getData();
                }
            }
            return $optionData;
        } catch (\Exception $exception) {
            //throw new \Magento\Framework\Exception\NoSuchEntityException(__('Such product doesn\'t exist'));
        }
        return $product;
    }

    /**
     * Updates quote item quantity.
     *
     * @param Item $item
     * @param float $qty
     * @return void
     * @throws LocalizedException
     */
    private function updateItemQuantity(Item $item, float $qty)
    {
        if ($qty > 0) {
            $item->clearMessage();
            $item->setQty($qty);

           if ($item->getHasError()) {
                throw new LocalizedException(__($item->getMessage()));
            }
        }
    }

    /**
     * JSON response builder.
     *
     * @param string $error
     * @return void
     */
    private function jsonResponse(string $error = '')
    {
        $this->getResponse()->representJson(
            $this->json->serialize($this->getResponseData($error))
        );
    }

    /**
     * Returns response data.
     *
     * @param string $error
     * @return array
     */
    private function getResponseData(string $error = ''): array
    {
        $response = ['success' => true];

        if (!empty($error)) {
            $response = [
                'success' => false,
                'error_message' => $error,
            ];
        }

     

        return $response;
    }

    /**
     * Validates the Request HTTP method
     *
     * @return void
     * @throws NotFoundException
     */
    private function validateRequest()
    {
        if ($this->getRequest()->isPost() === false) {
            throw new NotFoundException(__('Page Not Found'));
        }
    }

    /**
     * Validates form key
     *
     * @return void
     * @throws LocalizedException
     */
    private function validateFormKey()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(
                __('Something went wrong while saving the page. Please refresh the page and try again.')
            );
        }
    }

    /**
     * Validates cart data
     *
     * @param array|null $cartData
     * @return void
     * @throws LocalizedException
     */
    private function validateCartData($cartData = null)
    {
        if (!is_array($cartData)) {
            throw new LocalizedException(
                __('Something went wrong while saving the page. Please refresh the page and try again.')
            );
        }
    }
}
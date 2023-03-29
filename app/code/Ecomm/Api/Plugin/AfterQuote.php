<?php

/**
 * Copyright © 2018 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecomm\Api\Plugin;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\InputException;
use Ecomm\PriceEngine\Model\RestrictionProduct;
use Magento\Catalog\Model\ProductRepository;
use Ecomm\PriceEngine\Block\CustomPriceLogic;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\Quote;
use Plumrocket\CartReservation\Helper\Data;
use Plumrocket\CartReservation\Helper\Item;
use Plumrocket\CartReservation\Model\Config\Source\TimerMode;
use Plumrocket\CartReservation\Model\Config\Source\TimerType;

use Magento\Quote\Api\Data\CartItemInterface;

class AfterQuote {

    /**
     * @var \Magento\Quote\Api\Data\CartItemExtensionFactory
     */
    protected $cartItemExtension;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Authorization\Model\CompositeUserContext
     */
    protected $userContext;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    private $modelProduct;

    private $modelProductOption;

    private $checkoutSession;

    protected $_date;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var RestrictionProduct
     */
    private $restrictionProduct;

    /**
     * @var CustomPriceLogic
     */
    private $customPriceLogic;

    /**
     * @var RequestInterface
     */
    private $request;
    private    $cartItemInterface;
    private    $quote;

     /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Product
     */
    private $productHelper;

    /**
     * @var Item
     */
    private $itemHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;


    /**
     * @param \Magento\Quote\Api\Data\CartItemExtensionFactory $cartItemExtension
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param RestrictionProduct $restrictionProduct
     * @param CustomPriceLogic $customPriceLogic
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\Quote\Api\Data\CartItemExtensionFactory $cartItemExtension,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepository,
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $salableQuantity,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $_productTypeConfigurable,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Catalog\Helper\Output $catalogHelper,
        \Magento\Catalog\Model\Product $modelProduct,
        \Magento\Catalog\Model\Product\Option $modelProductOption,
        \Magento\Checkout\Model\Session $checkoutSession,
        CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Authorization\Model\CompositeUserContext $userContext,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        LoggerInterface $logger,
        RestrictionProduct $restrictionProduct,
        CustomPriceLogic $customPriceLogic,
        RequestInterface $request,
        CartItemInterface $cartItemInterface,
        Quote $quote,
        Data $dataHelper,
        \Plumrocket\CartReservation\Helper\Config $configHelper,
        Item $itemHelper,
        \Plumrocket\CartReservation\Helper\Product $productHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->cartItemExtension        = $cartItemExtension;
        $this->productRepository        = $productRepository;
        $this->salableQuantity          = $salableQuantity;
        $this->_productTypeConfigurable = $_productTypeConfigurable;
        $this->urlInterface             = $urlInterface;
        $this->catalogHelper            = $catalogHelper;
        $this->modelProduct             = $modelProduct;
        $this->modelProductOption       = $modelProductOption;
        $this->checkoutSession          = $checkoutSession;
        $this->customerRepositoryInterface          = $customerRepositoryInterface;
        $this->userContext              = $userContext;
        $this->_date                    = $date;
        $this->_logger                  = $logger;
        $this->restrictionProduct       = $restrictionProduct;
        $this->customPriceLogic         = $customPriceLogic;
        $this->request                  = $request;
        $this->cartItemInterface        = $cartItemInterface;
        $this->quote                    = $quote;
        $this->dataHelper = $dataHelper;
        $this->itemHelper = $itemHelper;
        $this->configHelper = $configHelper;
        $this->productHelper = $productHelper;
        $this->dateTime = $dateTime;
    }

    /**
     * Add attribute values
     *
     * @param   \Magento\Quote\Api\CartRepositoryInterface $subject,
     * @param   $quote
     * @return  $quoteData
     */
    public function afterSave(
    \Magento\Quote\Api\CartItemRepositoryInterface $subject, $item, $cartItem
    ) {
        $quoteId = $item->getQuoteId();
        $quote = $this->quote->load($quoteId);
        $quoteData = $this->setAttributeValue($quote, $item, $cartItem);
        return $quoteData;
    }

    /**
     * set value of attributes
     *
     * @param   $product,
     * @return  $extensionAttributes
     */
    private function setAttributeValue($quote, $item, $cartItem) 
    {
        $extensionAttributes = $item->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->cartItemExtension->create();
        }
        $validation = $this->itemValidation($item, 2);
        if(!empty($validation)){
            $extensionAttributes->setError($validation['message']);
            $item->setQty($validation['qty']);
            // $item->save();
        }
        $item->setExtensionAttributes($extensionAttributes);

        if($cartItem->getData('extension_attributes') && 
        $cartItem->getData('extension_attributes')->getPriceType()){
            $price = $item->getPrice();
            $priceTypeValue = 'Price';
            $priceType =   $cartItem->getData('extension_attributes')->getPriceType();
            $productData = $this->productRepository->create()->get($item->getSku());
            if($priceType == 'regular'){
                $priceValue =   $this->customPriceLogic->getCustomRegularPrice(
                    $this->userContext->getUserId(), $productData);
                    $price = $priceValue['price'];
                    $priceTypeValue = $priceValue['price_type'];
                   
               
            }else if($priceType == 'sub_wac'){
                $priceValue =  $this->customPriceLogic->get340bPrice("sub_wac", $productData);
                $price = $priceValue['price'];
                $priceTypeValue = $priceValue['price_type'];
               
            }else if($priceType == 'phs_indirect'){
                $priceValue =  $this->customPriceLogic->get340bPrice("phs_indirect", $productData);
                $price = $priceValue['price'];
                $priceTypeValue = $priceValue['price_type'];
            }
            $item->setCustomPrice($price);
            $item->setBasePrice($price);
            $item->setPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->setData('price_type', $priceTypeValue);
        }
        $item = $this->restrictionTime($item);
        $item->save();
        $quote->collectTotals();
        $quote->save();

        return $item;
    }

    public function itemValidation($product, $customerId){
        $pro =  $this->productRepository->create()->get($product->getSku());
        $res = $this->restrictionProduct->productRestrictions(
            $pro,$product->getQty(), $customerId);
            if(!empty($res)){
               return $res;
            }
        return [];
    }

    private function restrictionTime($quoteItem){

        if (! $this->dataHelper->moduleEnabled()) {
            return $quoteItem;
        }
        

        // Remember product ids
        $productIds = $this->dataHelper->getProductIds();
        $productIds[] = $quoteItem->getProductId();
        $this->dataHelper->setProductIds($productIds);

        switch (true) {
            /**
             * Disable reservation for product
             * (check it first, because if reservation disabled then guest mode doesn't matter)
             */
            case false === $this->productHelper->reservationEnabled($quoteItem->getProductId())
                && ($expireAt = Item::RESERVATION_DISABLED):
                // no break
            /**
             * Disable reservation for virtual products.
             */
            case $this->productHelper->isVirtual($quoteItem->getProduct())
                && ($expireAt = Item::RESERVATION_DISABLED):
                // no break

            /**
             * Default reservation logic.
             */
            default:
                $expireAt = $this->dataHelper->getExpireAt(
                    $this->configHelper->getCartTime()
                );

                // Add timer data. Don't save, it will be later.
                $quoteItem
                    ->setData('timer_expire_at', $expireAt)
                    ->setData('original_cart_expire_at', $expireAt);

                // If customer clicks the add to cart button, but last page is checkout then get checkout config time.
                if ($this->configHelper->getTimerMode() == TimerMode::SEPARATE
                    && $this->dataHelper->getTimerMode() == Data::TIMER_MODE_CHECKOUT
                ) {
                    $expireAt = $this->dataHelper->getExpireAt(
                        $this->configHelper->getCheckoutTime()
                    );

                    $quoteItem
                        ->setData('timer_expire_at', $expireAt)
                        ->setData('original_checkout_expire_at', $expireAt)
                        ->setData('cart_time', $expireAt - $this->dateTime->gmtTimestamp());
                }

                // If time is global for all items then update them.
                // Quote is missing for first item, so need to update global time starting with second item.
                if ($quoteItem->getQuoteId()
                    && ($this->configHelper->getCartReservationType() == TimerType::TYPE_GLOBAL
                        || $this->dataHelper->getTimerMode() == Data::TIMER_MODE_CHECKOUT)
                ) {
                    $this->itemHelper->updateGlobalTimer($quoteItem->getQuoteId(), 0, null, $expireAt);
                }
                break;
        }
        $quoteItem->save();
        return $quoteItem;

    }
}


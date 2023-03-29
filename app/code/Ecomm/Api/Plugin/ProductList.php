<?php

namespace Ecomm\Api\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Ecomm\HinEligibilityCheck\Block\exclusivePrice;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Ecomm\PriceEngine\Block\CustomPriceLogic;

class ProductList
{
    protected $productExtensionFactory;
    protected $productFactory;
    protected $helper;

    public function __construct(
        \Magento\Catalog\Api\Data\ProductExtensionFactory $productExtensionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ecomm\Theme\Helper\Output $helper,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        exclusivePrice $exclusivePrice,
        CustomerRepositoryInterface $customerRepository,
        CustomPriceLogic $priceLogicBlock,
        \Magento\Framework\UrlInterface $urlInterface
    )
    {
        $this->productFactory = $productFactory;
        $this->productExtensionFactory = $productExtensionFactory;
        $this->helper = $helper;
        $this->_userContext = $userContext;
        $this->exclusivePrice = $exclusivePrice;
        $this->customerRepository = $customerRepository;
        $this->priceLogicBlock = $priceLogicBlock;
        $this->urlInterface = $urlInterface;
    }

    public function afterGet(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $entity
    )
    {
        /** @var ProductInterface $product */
        $product = $entity;
        if (str_contains($this->urlInterface->getCurrentUrl(), 'rest/V1/ecomm-api/products')) {
            $customerId = $this->_userContext->getUserId();
            $subWacPrice = '';
            $phsIndirectPrice = '';
            $addressCollection = $this->exclusivePrice->getAddressCollection();
            if ($customerId) {
                $customerInfo = $this->customerRepository->getById($customerId);
                $shippingAddressId = $customerInfo->getDefaultShipping();
                $addressInfo = $addressCollection->getById($shippingAddressId);
                $hinStatus = '';
                if ($addressInfo->getCustomAttribute('hin_status')) {
                    $hinStatus = $addressInfo->getCustomAttribute('hin_status')->getValue();
                }
            }
            if ($customerId) {
                $regularprice = $this->priceLogicBlock->getCustomRegularPrice($customerId, $entity);
                $regularPrices = '$' . number_format($regularprice['price'], 2);
            }
            if ($hinStatus == "1") {
                $subWac = $this->priceLogicBlock->get340bPrice('sub_wac', $entity);
                if (isset($subWac['price'])) {
                    $subWacPrice = '$' . $subWac['price'];
                }
                $phsIndirect = $this->priceLogicBlock->get340bPrice('phs_indirect', $entity);
                if (isset($phsIndirect['price'])) {
                    $phsIndirectPrice = '$' . $phsIndirect['price'];
                }
            }
            // Fetch the raw product model (I have not found a better way), and set the data onto our attribute.
            //$productModel = $this->productFactory->create()->load($product->getId());
            $extensionAttributes = $product->getExtensionAttributes(); /** get current extension attributes from product **/

            $stockval=$this->helper->getStockCheck($product->getSku(),$product->getId());
            $instock=$stockval['instock'];
            $custominstock=$stockval['custominstock'];

            $stock_status = 0;
            if ($instock || $custominstock){
                $stock_status = 1;
            }

            $extensionAttributes->setStoctStatus($stock_status);
            $extensionAttributes->setCustomPrice($regularPrices);
            $extensionAttributes->setSubWacPrice($subWacPrice);
            $extensionAttributes->setPhsIndirectPrice($phsIndirectPrice);
            $product->setExtensionAttributes($extensionAttributes);
        }
        return $product;
    }

    public function afterGetList(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterface $searchCriteria
    ) : \Magento\Catalog\Api\Data\ProductSearchResultsInterface
    {
        $products = [];
        if (str_contains($this->urlInterface->getCurrentUrl(), 'rest/V1/ecomm-api/products')) {
            $customerId = $this->_userContext->getUserId();
            $addressCollection = $this->exclusivePrice->getAddressCollection();
            if ($customerId) {
                $customerInfo = $this->customerRepository->getById($customerId);
                $shippingAddressId = $customerInfo->getDefaultShipping();
                $addressInfo = $addressCollection->getById($shippingAddressId);
                $hinStatus = '';
                if ($addressInfo->getCustomAttribute('hin_status')) {
                    $hinStatus = $addressInfo->getCustomAttribute('hin_status')->getValue();
                }
            }
            foreach ($searchCriteria->getItems() as $entity) {
                /** @var ProductInterface $product */
                // Fetch the raw product model (I have not found a better way), and set the data onto our attribute.
                //$productModel = $this->productFactory->create()->load($product->getId());
                $extensionAttributes = $entity->getExtensionAttributes(); /** get current extension attributes from product **/

                $stockval=$this->helper->getStockCheck($entity->getSku(),$entity->getId());
                $instock=$stockval['instock'];
                $custominstock=$stockval['custominstock'];

                $stock_status = 0;
                if ($instock || $custominstock){
                    $stock_status = 1;
                }

                $price = array();
                $finalPrice = array();
                $customMinPrice = '';
                $customMaxPrice = '';
                if ($customerId) {
                    $regularprice = $this->priceLogicBlock->getCustomRegularPrice($customerId, $entity);
                    $price[] = $regularprice['price'];
                if ($hinStatus == "1") {
                    $subWac = $this->priceLogicBlock->get340bPrice('sub_wac', $entity);
                    if (isset($subWac['price'])) {
                        $price[] = $subWac['price'];
                    }
                    $phsIndirect = $this->priceLogicBlock->get340bPrice('phs_indirect', $entity);
                    if (isset($phsIndirect['price'])) {
                        $price[] = $phsIndirect['price'];
                    }
                    foreach ($price as $prices) {
                        if ($prices == 0) {
                            continue;
                        } else {
                            $finalPrice[] = $prices;
                        }
                    }
                } else {
                    foreach ($price as $prices) {
                        if ($prices == 0) {
                            continue;
                        } else {
                            $finalPrice[] = $prices;
                        }
                    }
                }
                }
                if ($customerId && isset($finalPrice) && $finalPrice != null) {
                    $customMinPrice = '$' .number_format(min($finalPrice), 2);
                    $customMaxPrice = '$' . number_format(max($finalPrice), 2);
                }

                $extensionAttributes->setStoctStatus($stock_status);
                $extensionAttributes->setCustomMinPrice($customMinPrice);
                $extensionAttributes->setCustomMaxPrice($customMaxPrice);
                $entity->setExtensionAttributes($extensionAttributes);

                $products[] = $entity;
            }
            $searchCriteria->setItems($products);
        }
        return $searchCriteria;
    }
}
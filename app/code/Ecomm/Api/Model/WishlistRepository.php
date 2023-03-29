<?php
/**
 * @copyright: Copyright © 2017 mediaman GmbH. All rights reserved.
 * @see LICENSE.txt
 */

namespace Ecomm\Api\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Wishlist\Model\ResourceModel\Item as ItemResource;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Ecomm\Api\Api\WishlistInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\CatalogInventory\Api\StockStateInterface;
use Ecomm\Api\Api\WishlistRepositoryInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Wishlist\Model\WishlistFactory;
use Ecomm\PriceEngine\Block\CustomPriceLogic;
use Ecomm\HinEligibilityCheck\Block\exclusivePrice;
use Magento\Customer\Api\CustomerRepositoryInterface;


/**
 * Class WishlistRepository
 * @package Ecomm\Api\Model
 */
class WishlistRepository implements WishlistRepositoryInterface
{

    /**
     * @var Http
     */
    private $http;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ItemResource
     */
    private $itemResource;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * WishlistRepository constructor.
     * @param Http $http
     * @param TokenFactory $tokenFactory
     * @param WishlistFactory $wishlistFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ItemResource $itemResource
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Http $http,
        TokenFactory $tokenFactory,
        WishlistFactory $wishlistFactory,
        ProductRepositoryInterface $productRepository,
        ItemResource $itemResource,
        CollectionFactory $wishlistCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storemanagerinterface,
        \Magento\Catalog\Model\Product $productload,
        Configurable $configurableProduct,
        EventManager $eventManager,
        StockStateInterface $stockState,
        GetSalableQuantityDataBySku $salableQuantity,
        CustomerSession $customerSession,
        CustomPriceLogic $priceLogicBlock,
        exclusivePrice $exclusivePrice,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->http = $http;
        $this->tokenFactory = $tokenFactory;
        $this->wishlistFactory = $wishlistFactory;
        $this->productRepository = $productRepository;
        $this->itemResource = $itemResource;
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->storemanagerinterface = $storemanagerinterface;
        $this->_productload = $productload;
        $this->customerSession = $customerSession;
        $this->eventManager = $eventManager;
        $this->stockState = $stockState;
        $this->salableQuantity = $salableQuantity;
        $this->configurableProduct = $configurableProduct;
        $this->priceLogicBlock = $priceLogicBlock;
        $this->exclusivePrice = $exclusivePrice;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     */
    public function getCurrent()
    {
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            $authorizationHeader = $this->http->getHeader('Authorization');

            $tokenParts = explode('Bearer', $authorizationHeader);
            $tokenPayload = trim(array_pop($tokenParts));

            /** @var Token $token */
            $token = $this->tokenFactory->create();
            $token->loadByToken($tokenPayload);

            $customerId = $token->getCustomerId();
        }


        /** @var Wishlist $wishlist */
        $wishlist = $this->wishlistFactory->create();
        $wishlist->loadByCustomerId($customerId);

        if (!$wishlist->getId()) {
            $wishlist->setCustomerId($customerId);
            $wishlist->getResource()->save($wishlist);
        }

        return $wishlist;
    }

    /**
     * @return WishlistData
     */
    public function getWishlistForCustomer()
    {
        $customerId = [];
        if (!$customerId) {
            $authorizationHeader = $this->http->getHeader('Authorization');

            $tokenParts = explode('Bearer', $authorizationHeader);
            $tokenPayload = trim(array_pop($tokenParts));

            /** @var Token $token */
            $token = $this->tokenFactory->create();
            $token->loadByToken($tokenPayload);

            $customerId = $token->getCustomerId();
        }
        
        $collection = [];
        if($customerId){
            $collection = $this->_wishlistCollectionFactory->create()->addCustomerIdFilter($customerId);
        }

        $baseurl = $this->storemanagerinterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';

        $wishlistData = [];
        foreach ($collection as $item) {
            $productInfo = $item->getProduct()->toArray();

            //get final prices
            // $prices = $this->getPriceRange($productInfo, $groupId);
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

        $price = array();
        $finalPrice = array();
        
        $regularprice = $this->priceLogicBlock->getCustomRegularPrice($customerId, $item->getProduct());
        $price[] = $regularprice['price'];

         if ($hinStatus == "1") {
                $subWac = $this->priceLogicBlock->get340bPrice('sub_wac', $item->getProduct());
                if (isset($subWac['price'])) {
                    $price[] = $subWac['price'];
                }
                $phsIndirect = $this->priceLogicBlock->get340bPrice('phs_indirect', $item->getProduct());
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
            if ($customerId && isset($finalPrice) && $finalPrice != null) {
                $productInfo['custom_min_price'] = '$' .number_format(min($finalPrice), 2);
                $productInfo['custom_max_price'] = '$' . number_format(max($finalPrice), 2);
            }

            //set min price
            if (isset($productInfo['min_price']) && isset($prices['min'])) {
                $productInfo['minimal_price'] = $prices['min'];
                $productInfo['min_price'] = $prices['min'];
            }

            //set max price
            if (isset($productInfo['max_price']) && isset($prices['max'])) {
                $productInfo['max_price'] = $prices['max'];
            } 
            if($item->getProduct()->getTypeId() == 'simple'){
                $salable_quantities = $this->salableQuantity->execute($item->getProduct()->getSku());
            } else {
                $salable_quantities = 0;
            }

            // if ($productInfo['small_image'] == 'no_selection') {
            //   $currentproduct = $this->_productload->load($productInfo['entity_id']);
            //   $imageURL = $this->getImageUrl($currentproduct, 'product_base_image');
            //   $productInfo['small_image'] = $imageURL;
            //   $productInfo['thumbnail'] = $imageURL;
            // }else{
            //   $imageURL = $baseurl.$productInfo['small_image'];
            //   $productInfo['small_image'] = $imageURL;
            //   $productInfo['thumbnail'] = $imageURL;
            // }
            $data = [
                "wishlist_item_id" => $item->getWishlistItemId(),
                "wishlist_id"      => $item->getWishlistId(),
                "product_id"       => $item->getProductId(),
                "store_id"         => $item->getStoreId(),
                "added_at"         => $item->getAddedAt(),
                "description"      => $item->getDescription(),
                "qty"              => round($item->getQty()),
                "salable_qty"      => $salable_quantities,
                "product"          => $productInfo
            ];
            $wishlistData[] = $data;
        }     

        return $wishlistData;
    }

    /**
     * Helper function that provides full cache image url
     * @param \Magento\Catalog\Model\Product
     * @return string
     */
    public function getImageUrl($product, string $imageType = ''){
        $storeId = $this->storemanagerinterface->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        $imageUrl = $this->productImageHelper->create()->init($product, $imageType)->getUrl();
        $this->appEmulation->stopEnvironmentEmulation();
    
        return $imageUrl;
    }

    /**
     * @param $product
     * @return array
     */
    public function getPriceRange($product, $groupId)
    {
        $childProductPrice = [];
        $childProducts = $this->configurableProduct->getUsedProducts($product);
        foreach ($childProducts as $child) {
            $qty = $this->getStockQty($child->getId());
            if ($child->isSaleable() && $qty>0) {
                $price = $child->getPrice();
                $finalPrice = $child->getFinalPrice();

                $child->setFinalPrice($finalPrice);
                $child->setCustomerGroupId($groupId);

                $this->eventManager->dispatch('catalog_product_get_final_price', ['product' => $child, 'qty' => $qty]);
                $finalPrice = $child->getData(self::FINAL_PRICE);

                if ($price == $finalPrice) {
                    $childProductPrice[] = $price;
                } else if ($finalPrice < $price) {
                    $childProductPrice[] = $finalPrice;
                }
            }
        }

        $min = min($childProductPrice);
        $max = max($childProductPrice);

        return [
            self::MIN => $min,
            self::MAX => $max
        ];
    }

    /**
     * @param $productId
     * @param null $websiteId
     * @return float
     */
    public function getStockQty($productId, $websiteId = null)
    {
        return $this->stockState->getStockQty($productId, $websiteId);
    }

    /**
     * @inheritdoc
     */
    public function addItem(string $sku): bool
    {
        $product = $this->productRepository->get($sku);
        $wishlist = $this->getCurrent();

        $wishlist->addNewItem($product);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(int $itemId): bool
    {
        $wishlist = $this->getCurrent();

        $item = $wishlist->getItem($itemId);
        if (!$item) {
            return false;
        }

        $this->itemResource->delete($item);

        return true;
    }


    /**
     * @inheritdoc
     */
    public function updateItem(int $itemId,int $qty)
    {
        $wishlist = $this->getCurrent();

        $wishlist->updateItem($itemId, new \Magento\Framework\DataObject(['qty' => $qty]));

        return true;
    }
}

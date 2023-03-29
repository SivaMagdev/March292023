<?php
namespace Magecomp\Ajaxsearch\Model\Autocomplete;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magecomp\Ajaxsearch\Helper\Data as AjaxHelper;
use Magento\Checkout\Helper\Cart;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Eav\Model\Config as eavConfig;
use Magento\Framework\App\Helper\Context;
use Ecomm\Theme\Helper\Output as ThemeHelper;
use Ecomm\HinEligibilityCheck\Block\exclusivePrice;
use Ecomm\PriceEngine\Block\CustomPriceLogic;

class Ajaxsearchdataprovider implements DataProviderInterface
{
    protected $queryFactory;
    protected $itemFactory;
    protected $productRepository;
    protected $searchCriteriaBuilder;
    protected $priceCurrency;
    protected $productHelper;
    protected $imageHelper;
    protected $layerResolver;
    protected $ajaxhelper;
    protected $themeHelper;
    protected $exclusivePrice;
    protected $priceLogicBlock;

    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PriceCurrencyInterface $priceCurrency,
        Image $imageHelper,
        LayerResolver $layerResolver,
        AjaxHelper $ajaxhelper,
        Cart $cart,
        CollectionFactory $collectionFactory,
        GetProductSalableQtyInterface $getProductSalableQtyInterface,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        eavConfig $eavConfig,
        ThemeHelper $themeHelper,
        Context $context,
        exclusivePrice $exclusivePrice,
        CustomPriceLogic $priceLogicBlock
    )
    {
        $this->queryFactory = $queryFactory;
        $this->itemFactory  = $itemFactory;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->priceCurrency = $priceCurrency;
        $this->imageHelper = $imageHelper;
        $this->layerResolver = $layerResolver;
        $this->ajaxhelper = $ajaxhelper;
        $this->cart = $cart;
        $this->collectionFactory = $collectionFactory;
        $this->getProductSalableQtyInterface = $getProductSalableQtyInterface;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->eavConfig = $eavConfig;
        $this->themeHelper = $themeHelper;
        $this->exclusivePrice = $exclusivePrice;
        $this->priceLogicBlock = $priceLogicBlock;
        $this->request = $context->getRequest();
    }

    public function getItems()
    {

        $result     = [];   
        $query      = $this->queryFactory->get()->getQueryText();
        $attribute_code = $this->request->getParam('custom_attribute');
        $productIds = $this->searchProductsFullText($query, $attribute_code);

        $addressCollection = $this->exclusivePrice->getAddressCollection();
        if ($this->customerSession->isLoggedIn()) {
            $customerInfo = $this->customerRepository->getById($this->customerSession->getId());
            $shippingAddressId = $customerInfo->getDefaultShipping();
            $addressInfo = $addressCollection->getById($shippingAddressId);
            $hinStatus = '';
            if ($addressInfo->getCustomAttribute('hin_status')) {
                $hinStatus = $addressInfo->getCustomAttribute('hin_status')->getValue();
            }
        }


        if ($productIds)
        {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $productIds, 'in')->create();
            $products = $this->productRepository->getList($searchCriteria);
            $_loggedin = $this->cart->getCart()->getCustomerSession()->isLoggedIn();
            $_customerApproved = $this->getCustomerApproved();
            $priceRange = '';
            
            foreach ($products->getItems() as $product)
            {
                $price = array();
                $finalPrice = array();
                if ($this->customerSession->isLoggedIn()) {
                    $regularprice = $this->priceLogicBlock->getCustomRegularPrice($this->customerSession->getId(), $product);
                    $price[] = $regularprice['price'];

                if ($hinStatus == "1") {
                    $subWac = $this->priceLogicBlock->get340bPrice('sub_wac', $product);
                    if (isset($subWac['price'])) {
                        $price[] = $subWac['price'];
                    }
                    $phsIndirect = $this->priceLogicBlock->get340bPrice('phs_indirect', $product);
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

                if ($this->customerSession->isLoggedIn() && isset($finalPrice) && $finalPrice != null) {
                    $priceRange = '$' .number_format(min($finalPrice), 2) . " - " . '$' . number_format(max($finalPrice), 2);
                }

                $image = $this->imageHelper->init($product, 'product_page_image_small')->getUrl();

                $stockval=$this->themeHelper->getStockCheck($product->getSku(),$product->getId());
                $_productStock = $this->themeHelper->getStockItem($product->getId());

                $instock=$stockval['instock'];
                $custominstock=$stockval['custominstock'];
                $stock_status = 0;

                if (($instock && $_productStock->getQty() >0) || $custominstock){
                    $stock_status = 1;
                }

                $resultItem = $this->itemFactory->create([
                    'title'             => $product->getName(),
                    'sku'               => $product->getSku(),
                    'loggedin'          => $_loggedin,
                    'customerApproved'  => $_customerApproved,
                    'strength'          => $product->getResource()->getAttribute('strength')->setStoreId(0)->getFrontend()->getValue($product),
                    'pack_size'         => $product->getResource()->getAttribute('pack_size')->setStoreId(0)->getFrontend()->getValue($product),
                    'case_pack'         => $product->getResource()->getAttribute('case_pack')->setStoreId(0)->getFrontend()->getValue($product),
                    'stock_item'        => $this->getProductSalableQtyInterface->execute($product->getSku(), $product->getStore()->getWebsiteId()),
                    'stock_status'      => $stock_status,
                    'final_price'       => $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(),
                    'price'             => $priceRange,
                    'special_price'     => $this->priceCurrency->format($product->getPriceInfo()->getPrice('special_price')->getAmount()->getValue(),false),
                    'has_special_price' => $product->getSpecialPrice() > 0 ? true : false,
                    'image'             => $image,
                    'url'               => $product->getProductUrl()
                ]);
                $result[]   = $resultItem;
            }
        }

        return $result;
    }

    protected function searchProductsFullText($query, $attribute_code)
    {
        if($this->ajaxhelper->isEnabled()) :
            if($attribute_code){
                $attribute_code_values = explode('-', $attribute_code);
                $productCollection =  $this->collectionFactory->create();
                $productCollection->setPageSize($this->ajaxhelper->getProductCount());
                $productCollection->addAttributeToFilter($attribute_code_values[0],['eq' => $attribute_code_values[1]]);
                $productCollection->addAttributeToFilter(array(
                                                array('attribute' => 'name', 'like' => '%'.$query.'%')
                                            ));
            }else{
                // $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);
                // $productCollection = $this->layerResolver->get()
                //     ->getProductCollection()
                //     ->setPageSize($this->ajaxhelper->getProductCount())
                //     ->addSearchFilter($query);

                //select sku from catalog_product_entity WHERE replace(replace(replace(sku,'-',''),'(',''),')','') LIKE '%435980%'

                //$select->where('entity_id IN (?)', ['1', '2', '3']);

                $productCollection = $this->collectionFactory->create();
                $productCollection->addAttributeToSelect('*');
                $productCollection->addAttributeToFilter(array(
                                                array('attribute' => "name", 'like' => $query.'%'),
                                                array('attribute' => "sku", 'like' => '%'.$query.'%'),
                                                array('attribute' => "ndc", 'like' => '%'.$query.'%')
                                            ));
                //$conditionSql = "(e.`sku` LIKE '%43598-0%')";
                //$productCollection->getSelect()->where('sku LIKE ?', '%43598-0%', \Magento\Framework\DB\Select::TYPE_CONDITION);
                //echo $productCollection->getSelect();
                $productCollection->setPageSize($this->ajaxhelper->getProductCount());

            }
            $productIds = [];
            foreach ($productCollection as $product) {
                $productIds[] = $product->getId();
            }
            return $productIds;
        endif;
    }

    protected function getCustomerApproved(){
        $application_verified_status = false;

        if($this->customerSession->isLoggedIn()) {

            if($this->customerSession->getId()) {

                $customerData = $this->customerRepository->getById($this->customerSession->getId());

                $attribute = $this->eavConfig->getAttribute('customer', 'application_status');
                $options = $attribute->getSource()->getAllOptions();
                $application_statuses = [];
                foreach ($options as $option) {
                    if ($option['value'] > 0) {
                        $application_statuses[$option['value']] = $option['label'];
                    }
                }
                $application_status = 0;
                $approved_id = array_search("Approved",$application_statuses);
                if($customerData->getCustomAttribute('application_status')){
                    $application_status = $customerData->getCustomAttribute('application_status')->getValue();
                }

                if($approved_id == $application_status){
                    $application_verified_status = true;
                }
            }
        }
        return $application_verified_status;
    }
}
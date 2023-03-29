<?php

namespace Ecomm\LayeredNavigation\Plugin\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

class Layer
{
/**
 * Product collections array
 *
 * @var array
 */
protected $_productCollections = [];

/**
 * Key which can be used for load/save aggregation data
 *
 * @var string
 */
protected $_stateKey = null;

/**
 * Core registry
 *
 * @var \Magento\Framework\Registry
 */
protected $registry = null;

/**
 * Store manager
 *
 * @var \Magento\Store\Model\StoreManagerInterface
 */
protected $_storeManager;

/**
 * Catalog product
 *
 * @var \Magento\Catalog\Model\ResourceModel\Product
 */
protected $_catalogProduct;

/**
 * Attribute collection factory
 *
 * @var AttributeCollectionFactory
 */
protected $_attributeCollectionFactory;

/**
 * Layer state factory
 *
 * @var \Magento\Catalog\Model\Layer\StateFactory
 */
protected $_layerStateFactory;

/**
 * @var \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface
 */
protected $collectionProvider;

/**
 * @var \Magento\Catalog\Model\Layer\Category\StateKey
 */
protected $stateKeyGenerator;

/**
 * @var \Magento\Catalog\Model\Layer\Category\CollectionFilter
 */
protected $collectionFilter;

/**
 * @var CategoryRepositoryInterface
 */
protected $categoryRepository;

protected $request;

protected $productCollectionFactory;

/**
 * @param Layer\ContextInterface $context
 * @param Layer\StateFactory $layerStateFactory
 * @param AttributeCollectionFactory $attributeCollectionFactory
 * @param \Magento\Catalog\Model\ResourceModel\Product $catalogProduct
 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
 * @param \Magento\Framework\Registry $registry
 * @param CategoryRepositoryInterface $categoryRepository
 * @param array $data
 */
public function __construct(
    \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
    AttributeCollectionFactory $attributeCollectionFactory,
    \Magento\Catalog\Model\ResourceModel\Product $catalogProduct,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\Registry $registry,
    CategoryRepositoryInterface $categoryRepository,
    \Magento\Framework\App\RequestInterface $request,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    array $data = []
) {
    $this->_layerStateFactory = $layerStateFactory;
    $this->_attributeCollectionFactory = $attributeCollectionFactory;
    $this->_catalogProduct = $catalogProduct;
    $this->_storeManager = $storeManager;
    $this->registry = $registry;
    $this->request = $request;
    $this->categoryRepository = $categoryRepository; 
    $this->productCollectionFactory = $productCollectionFactory;

}

public function aroundGetProductCollection(\Magento\Catalog\Model\Layer $subject, \Closure $proceed)
//public function afterGetProductCollection(\Magento\Catalog\Model\Layer $subject, $result)
{
    $result = $proceed();
    $getstate=$this->request->getParam('state');
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $customerSession = $objectManager->create('Magento\Customer\Model\Session');
    $StockState = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
    //$custom_helper = $this->helper('Ecomm\Theme\Helper\Output');

    $custom_helper = $objectManager->get('\Ecomm\Theme\Helper\Output');

    $productUniqueIds=[];

    $productSkus=[];

    if(isset($getstate) && $getstate == 'stock')
    {
        foreach($result as $product){
            $stockstatus=$custom_helper->getConfigVal('cataloginventory/options/display_product_stock_status');
            if($stockstatus)
            {
               // $rs = clone $product;
                /*Checking the whether instock or out of stock*/
                $stockval=$custom_helper->getStockCheck($product->getSku(),$product->getId());
                $instock=$stockval['instock'];
                $custominstock=$stockval['custominstock'];
                if ($instock || $custominstock){
                    $productUniqueIds[] = $product->getId();
                }
            }

        }

        $productUniqueIds=array_unique($productUniqueIds);
        
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['*','is_saleable']);
        $collection->addAttributeToFilter('entity_id', array('in' => implode(",",$productUniqueIds)));
        $collection->getSelect()->join(
            ['catalog_category_product'],
            'catalog_category_product.product_id=e.entity_id',
            []
           )->columns(['catalog_category_product.position as cat_index_position']);
        $collection->getSelect()->join(
            ['catalog_product_index_price'],
            'catalog_product_index_price.entity_id=e.entity_id',
            []
           )->columns(["catalog_product_index_price.price","catalog_product_index_price.tax_class_id","catalog_product_index_price.final_price","catalog_product_index_price.min_price as minimal_price","catalog_product_index_price.min_price","catalog_product_index_price.max_price","catalog_product_index_price.tier_price"]);

        
        $collection->getSelect()->group('e.entity_id');
        //$collection->addMinimalPrice();
        $collection->getSelect()->join(
            ['cataloginventory_stock_item'],
            'cataloginventory_stock_item.product_id=e.entity_id',
            []
           )->columns(['cataloginventory_stock_item.is_in_stock as is_salable']);

           

           //$result->addAttributeToFilter('entity_id', array('in' => implode(",",$productUniqueIds)));

          
           $collection->getSize();
           //$result=$collection;
          
          /* echo count($result);
           echo "<br>";
           echo count($collection);
           die();*/

         //  return $result;

        }

        
        return $result;
       
    
    
   
    }

    


}
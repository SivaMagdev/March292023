<?php

namespace Ecomm\ManageSeller\Plugin\Model;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Filter\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\App\ResourceConnection;

class IndexBuilder
{
    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $storeManager;


    public function __construct(
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    \Magento\Catalog\Model\Product\Visibility $productVisibility,
    \Magento\Catalog\Helper\Category $categoryHelper,
    \Magento\Framework\Registry $registry
    ) {
        $this->storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory; 
        $this->_productVisibility = $productVisibility;
        $this->categoryHelper = $categoryHelper;
        $this->registry = $registry;
        }

    /**
    * Build index query
    *
    * @param $subject
    * @param callable $proceed
    * @param RequestInterface $request
    * @return Select
    * @SuppressWarnings(PHPMD.UnusedFormatParameter)
    */
    public function aroundBuild($subject, callable $proceed, RequestInterface $request)
    {
        $select = $proceed($request);
        $storeId = $this->storeManager->getStore()->getStoreId();
        $rootCatId = $this->storeManager->getStore($storeId)->getRootCategoryId();
        $productUniqueIds = $this->getCustomCollectionQuery();
        $select->where('search_index.entity_id IN (' . join(',', $productUniqueIds) . ')');

        return $select;
    }

    /**
    *
    * @return ProductIds[]
    */
    public function getCustomCollectionQuery() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $StockState = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
        $custom_helper = $objectManager->get('\Ecomm\Theme\Helper\Output');

        
        $request = $objectManager->get('Magento\Framework\App\Request\Http');  
        $stockstatus=$custom_helper->getConfigVal('cataloginventory/options/display_product_stock_status');
        if($stockstatus)
        {                    

            $productUniqueIds=[];
            $category = $objectManager->get('Magento\Framework\Registry')->registry('current_category');//get current category
            $categoryId=0;
            if(isset($category)){
               
                $categoryId= $category->getId();
                $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');

                $category = $categoryFactory->create()->load($categoryId);
                $allCatProducts = $category->getProductCollection()->addAttributeToSelect('*');                    

                foreach($allCatProducts as $_product) {
                    //Checking the whether instock or out of stock
                    $stockval=$custom_helper->getStockCheck($_product->getSku(),$_product->getId());

                    $instock=$stockval['instock'];
                    $custominstock=$stockval['custominstock'];
                    if ($instock || $custominstock)
                        $productUniqueIds[]=$_product->getId();
                }
                
            }
            else
            {
                
                $collectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
                if($attribute_code){
                    $attribute_code_values = explode('-', $attribute_code);
                    $productCollection =  $collectionFactory->create();
                    //$productCollection->setPageSize($this->ajaxhelper->getProductCount());
                    $productCollection->addAttributeToFilter($attribute_code_values[0],['eq' => $attribute_code_values[1]]);
                    $productCollection->addAttributeToFilter(array(
                                                    array('attribute' => 'name', 'like' => '%'.$query.'%')
                                                ));
                }else{                       
    
                    $productCollection = $collectionFactory->create();
                    $productCollection->addAttributeToSelect('*');
                    $productCollection->addAttributeToFilter(array(
                                                    array('attribute' => "name", 'like' => $query.'%'),
                                                    array('attribute' => "sku", 'like' => '%'.$query.'%'),
                                                    array('attribute' => "ndc", 'like' => '%'.$query.'%')
                                                ));
                    
                    //$productCollection->setPageSize($this->ajaxhelper->getProductCount());
   
                }
               
                foreach ($productCollection as $product) {
                    $stockval=$custom_helper->getStockCheck($product->getSku(),$product->getId());
                                                
                    $instock=$stockval['instock'];
                    $custominstock=$stockval['custominstock'];
                   
                    if ($instock || $custominstock)
                        $productUniqueIds[]=$product->getId();                    
                        
                }               

            }            
           
        }
        $getProductUniqueIds = array_unique($getProductAllIds);
        return $getProductUniqueIds;
    }

}
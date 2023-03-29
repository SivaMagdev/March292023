<?php
namespace Ecomm\LayeredNavigation\Plugin\Model;

class Toolbar
{

  protected $_objectManager;
  protected $request;
  protected $productCollectionFactory;

  public function __construct(
    \Magento\Framework\ObjectManagerInterface $objectmanager,
    \Magento\Framework\App\Request\Http $request,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
  ) {
    $this->_objectManager = $objectmanager;
    $this->request = $request;
    $this->productCollectionFactory = $productCollectionFactory;
  }

  public function aroundSetCollection(
    \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
    \Closure $proceed,
    $request
  ) {
    $result = $proceed($request);

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
        foreach($request as $product){
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

          

           //$this->_productCollection = $collection;

        
         $request->addAttributeToFilter('entity_id', array('in' => implode(",",$productUniqueIds)));

    }
    
    $this->_collection = $request;
    $category = $this->_objectManager->get('Magento\Framework\Registry')->registry('current_category');
    if($category)
    {
      $page = $this->request->getParam('p');
      if($page == '')
      {
        $page = 1;
      }
      $this->_collection->getCurPage();
      $this->_collection->setCurPage($page);  
    }
    //else
    //{
    //  $this->_collection->setCurPage($this->getCurrentPage());
    //}

    return $result;
  }

}
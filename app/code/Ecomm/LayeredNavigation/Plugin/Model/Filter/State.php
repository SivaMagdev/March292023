<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Ecomm\LayeredNavigation\Plugin\Model\Filter;


use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection;
use Mageplaza\LayeredNavigationPro\Helper\Data;
use Zend_Db_Expr;

/**
 * Class State
 * @package Mageplaza\LayeredNavigationPro\Model\Layer\Filter
 */
class State extends \Mageplaza\LayeredNavigationPro\Model\Layer\Filter\State
{
    const OPTION_NEW = 'new';
    const OPTION_SALE = 'onsales';
    const OPTION_STOCK = 'stock';
    

    /**
     * @param $type
     * @param $collection
     *
     * @return mixed
     */
    protected function addFilterToCollection($type, $collection)
    {
        switch ($type) {
            case self::OPTION_NEW:
                $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
                $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

                /** @var Collection $collection */
                $collection
                    ->addAttributeToFilter('news_from_date', [
                        'or' => [
                            0 => ['date' => true, 'to' => $todayEndOfDayDate],
                            1 => ['is' => new Zend_Db_Expr('null')],
                        ]
                    ], 'left')
                    ->addAttributeToFilter('news_to_date', [
                        'or' => [
                            0 => ['date' => true, 'from' => $todayStartOfDayDate],
                            1 => ['is' => new Zend_Db_Expr('null')],
                        ]
                    ], 'left')
                    ->addAttributeToFilter([
                        ['attribute' => 'news_from_date', 'is' => new Zend_Db_Expr('not null')],
                        ['attribute' => 'news_to_date', 'is' => new Zend_Db_Expr('not null')],
                    ]);

                break;
            case self::OPTION_SALE:
                /** @var Collection $collection */
                $collection->getSelect()->where('price_index.final_price < price_index.price');

                break;
            //case self::OPTION_STOCK:
                //$this->stockHelper->addInStockFilterToCollection($collection);             
                // break;
        }

        return $collection;
    }

    

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $module_helper = $objectManager->get('\Mageplaza\LayeredNavigationPro\Helper\Data');

        $StockState = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
        $custom_helper = $objectManager->get('\Ecomm\Theme\Helper\Output');

        
        $request = $objectManager->get('Magento\Framework\App\Request\Http');  
        
        $stateConfig = $module_helper->getFilterConfig('state');
        $checkCount = false;
        $itemData = [];
        $options = [self::OPTION_NEW, self::OPTION_SALE, self::OPTION_STOCK];
        foreach ($options as $option) {
            if (!$stateConfig[$option . '_enable']) {
                continue;
            }

            if ($this->filterValue && in_array($option, $this->filterValue)) {

                $count = $productCollection->getSize();
            } else {
                $productCollectionClone = clone $productCollection;
                $this->addFilterToCollection($option, $productCollectionClone);

                $count = $productCollectionClone->resetTotalRecords()->getSize();
            }

            if ($count == 0 && !$module_helper->getFilterModel()->isShowZero($this)) {
                continue;
            }

            if ($count > 0) {
                $checkCount = true;
            }


            $getstate=$request->getParam('state');

            $query      = $request->getParam('q');
            $attribute_code = $request->getParam('custom_attribute');
          
           
            if(isset($option) && $option=='stock')
            {
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

                    
                    $count=count($productUniqueIds);
                }

               

            }

            $itemData[] = [
                'label' => $this->getOptionText($option),
                'value' => $option,
                'count' => $count
            ];


        }


        if ($checkCount) {
            foreach ($itemData as $item) {
                $this->itemDataBuilder->addItemData($item['label'], $item['value'], $item['count']);
            }
        }

        return $this->itemDataBuilder->build();
    }
}
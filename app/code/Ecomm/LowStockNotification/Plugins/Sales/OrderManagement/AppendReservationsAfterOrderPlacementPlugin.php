<?php

namespace Ecomm\LowStockNotification\Plugins\Sales\OrderManagement;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\InventoryReservationsApi\Model\GetReservationsQuantityInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Ecomm\LowStockNotification\Helper\Data as DataHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class AppendReservationsAfterOrderPlacementPlugin
{
    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $_stockByWebsiteIdResolver;

    /**
     * @var GetStockItemDataInterface
     */
    private $_getStockItemData;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $_getStockItemConfiguration;

    /**
     * @var GetReservationsQuantityInterface
     */
    private $_getReservationsQuantity;

    protected $stockItemRepository;

    protected $stockState;

    protected $modelProduct;

    protected $modelProductOption;

    protected $resourceConnection;

    protected $logger;

    /**
     * AppendReservationsAfterOrderPlacementPlugin constructor.
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param GetStockItemDataInterface $getStockItemData
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param GetReservationsQuantityInterface $getReservationsQuantity
     * @param StockRegistryInterface $stockItemRepository
     * @param GetSalableQuantityDataBySku $stockState
     * @param DataHelper $helper
     * @param Product $modelProduct
     * @param Option $modelProductOption
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        GetStockItemDataInterface $getStockItemData,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetReservationsQuantityInterface $getReservationsQuantity,
        StockRegistryInterface $stockItemRepository,
        GetSalableQuantityDataBySku $stockState,
        DataHelper $helper,
        Product $modelProduct,
        Option $modelProductOption,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->_stockByWebsiteIdResolver    = $stockByWebsiteIdResolver;
        $this->_getStockItemData            = $getStockItemData;
        $this->_getStockItemConfiguration   = $getStockItemConfiguration;
        $this->_getReservationsQuantity     = $getReservationsQuantity;
        $this->stockItemRepository          = $stockItemRepository;
        $this->helper                       = $helper;
        $this->stockState                   = $stockState;
        $this->modelProduct                 = $modelProduct;
        $this->modelProductOption           = $modelProductOption;
        $this->resourceConnection           = $resourceConnection;
        $this->logger                       = $logger;
    }

    /**
     * @param OrderManagementInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException
     */
    public function afterPlace(OrderManagementInterface $subject, OrderInterface $order) : OrderInterface
    {
        if ($this->helper->isEnabled()) {
            $websiteId = (int)$order->getStore()->getWebsiteId();
            $stock = $this->_stockByWebsiteIdResolver->execute((int)$websiteId);

            $stockId = (int)$stock->getStockId();
            $stockName = $stock->getName();
            $lowStockItems = [];

            /*$order_stdated_skus = [];
            $order_general_skus= [];

            foreach ($order->getAllVisibleItems() as $orderItem) {
                $sku = $orderItem->getSku();
                $item_options = $orderItem->getProductOptions();
                $qty_ordered = $orderItem->getQtyOrdered();

                $customOptions = [];
                if(isset($item_options['options'])){
                    $customOptions = $item_options['options'];
                    //$this->logger->info('Item Options:'.print_r($customOptions, true));
                    $order_stdated_skus = [
                        'qty'=>$qty_ordered,
                        'sku' => $sku
                    ];
                } else {
                    $order_general_skus = [
                        'qty'=>$qty_ordered,
                        'sku' => $sku
                    ];
                }
            }*/

            $order_stdated_skus = [];
            $order_stdated_qtys = [];
            $order_general_skus = [];
            $order_general_qtys = [];

            $cartItems = $order->getAllVisibleItems();

            if($cartItems){
                foreach($cartItems as $cartItem){

                    //echo $item->getId();
                    //echo $item->getQuoteId();

                    $sku = $cartItem->getSku();
                    $item_options = $cartItem->getProductOptions();
                    $qty_ordered = $cartItem->getQtyOrdered();

                    //echo $item->getProduct()->getId();
                    $customOptions = [];
                    if(isset($item_options['options'])){
                        if(isset($order_stdated_skus[$sku])){
                            $order_stdated_skus[$sku] = $sku;
                            $order_stdated_qtys[$sku] += $qty_ordered;

                        } else {
                            $order_stdated_skus[$sku] = $qty_ordered;
                            $order_stdated_qtys[$sku] = $qty_ordered;
                        }
                    } else {
                        $order_general_skus[$sku] = $sku;
                        $order_general_qtys[$sku] = $qty_ordered;
                    }
                }
            }

            //$this->logger->info('order_stdated_skus: '.print_r($order_stdated_skus, true));
            //$this->logger->info('order_general_skus: '.print_r($order_general_skus, true));
            //echo "<pre>".print_r($order_stdated_skus, true).'</pre>';
            //echo "<pre>".print_r($order_general_skus, true).'</pre>';

            if($order_stdated_skus) {
                foreach($order_general_skus as $idx=>$general_skus) {
                    if(in_array($general_skus, $order_stdated_skus)){

                        //echo 'sku#: '.$order_general_qtys[$idx].' - '.$order_stdated_qtys[$idx].'<br />';
                        //$this->logger->info('sku#: '.$order_general_qtys[$idx].'- '.$order_stdated_qtys[$idx]);

                    } else {
                        unset($order_general_skus[$idx]);
                        unset($order_general_qtys[$idx]);
                        //echo 'sku#:- '.$general_skus.'<br />';
                    }
                }
            }

            //echo "<pre>".print_r($order_general_skus, true).'</pre>';
            //echo "<pre>".print_r($order_general_qtys, true).'</pre>';
            //$this->logger->info('order_stdated_skus: '.print_r($order_general_skus, true));
            //$this->logger->info('order_general_qtys: '.print_r($order_general_qtys, true));

            foreach ($order->getAllVisibleItems() as $orderItem) {
                $sku = $orderItem->getSku();
                $item_options = $orderItem->getProductOptions();
                $qty_ordered = $orderItem->getQtyOrdered();

                //$this->logger->info('Order Item Options: '.print_r($item_options, true));

                $customOptions = [];
                if(isset($item_options['options'])){
                    $customOptions = $item_options['options'];
                    //$this->logger->info('Item Options:'.print_r($customOptions, true));
                }

                if (!empty($customOptions)) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    foreach($customOptions as $customOption){
                        $option_type_id = $customOption['option_value'];
                        $option_id = $customOption['option_id'];
                    }

                    //$this->logger->info('option_value: '.$option_type_id.'option_id: '.$option_id);

                    $product = $this->modelProduct->load($orderItem->getProductId());

                    foreach ($product->getOptions() as $option) {
                        $option_values = $option->getValues();
                    }
                    $quantity_in_stock = 0;
                    foreach($option_values as $values){

                        if($values->getId() == $option_type_id){
                            $quantity_in_stock = $values->getQuantity();
                        }
                    }

                    $quantity_update = (int)$quantity_in_stock-$qty_ordered;

                    $connection = $this->resourceConnection->getConnection();

                    $optionUpdate = [
                        'quantity' => $quantity_update
                    ];

                    $connection->update(
                        $this->resourceConnection->getTableName('catalog_product_option_type_value'),
                        $optionUpdate,
                        ['option_type_id = ?' => (int)$option_type_id]
                    );

                    //$this->logger->info('Qty Update: '.print_r($optionUpdate, true));
                    if($order_general_qtys){
                        foreach($order_general_qtys as $gsku=>$gqty){

                            //$this->logger->info('Inventory reservation: '.$gsku.' Qty:'.($gqty*-1).' Order: '.$order->getIncrementId());

                            $res_qty_update = [
                                'quantity' => ($gqty*-1)
                            ];

                            $connection->update(
                                'inventory_reservation',
                                $res_qty_update,
                                ['sku LIKE ?' => $gsku, 'metadata LIKE ?' => '%'.$order->getIncrementId().'%']
                            );

                        }
                    }

                    //$connection->update('inventory_reservation', ['sku LIKE ?' => $sku], 'metadata LIKE ?' => '%'.$order->getIncrementId().'%');

                    //$tableName = $this->resourceConnection->getTableName('catalog_product_option_type_value');
                    //$sql = "Update " . $tableName . " Set quantity = " . $quantity_update . " where option_type_id = " . $option_type_id;
                    //$this->logger->info($sql);

                    /*$stockItem = $this->stockItemRepository->getStockItemBySku($sku);
                    $stockItemData = $this->_getStockItemData->execute($sku, $stockId);
                    $salable_quantities = $this->stockState->execute($sku, $stockId);
                    $this->logger->info('saleable Inventory: '.print_r($salable_quantities, true));
                    //echo $salable_quantities[0]['qty']
                    $qty_available = $salable_quantities[0]['qty'];
                    $qty_update = (int)$qty_available+$qty_ordered;*/
                    //$qty_available = $stockItemData[GetStockItemDataInterface::QUANTITY];
                    //$this->logger->info('qty_available:'.$qty_available.'qty_ordered: '.$qty_ordered);
                    //$qty_available = 947;
                    //$stockItem->setQty($qty_update);
                    /*$stockItem->setIsInStock((bool)$qty_update); // this line

                    $this->stockItemRepository->updateStockItemBySku($sku, $stockItem);*/

                    /*$values = array();
                    foreach ($product->getOptions() as $o) {
                        $p = $o->getValues();
                    }
                    foreach($p as $v){
                        $values[$v->getId()]['option_type_id']= $v->getId();
                        $values[$v->getId()]['title']= $v->getTitle();
                        $values[$v->getId()]['price']= $v->getPrice();
                        $values[$v->getId()]['price_type']= 'fixed';
                        $values[$v->getId()]['sku']= $v->getSku();
                        $values[$v->getId()]['expiry_date']= $v->getExpiryDate();
                        $values[$v->getId()]['sort_order']= $v->getSortOrder();
                        $values[$v->getId()]['store_id']= 0;
                        if($option_type_id == $v->getId()) {
                            $values[$v->getId()]['quantity']= (int)$v->getQuantity()-$qty_ordered;
                        } else {
                            $values[$v->getId()]['quantity']= (int)$v->getQuantity();
                        }

                    }
                    $optionsArray = [
                        [
                            'option_id' => $option_id,
                            'title' => 'Short Dated LOT No#',
                            'type' => 'radio',
                            'is_require' => 0,
                            'sort_order' => 1,
                            'store_id' => 0,
                            'values' => $values,
                        ]
                    ];
                    $this->logger->info('Item Options:'.print_r($optionsArray, true));
                    foreach ($optionsArray as $optionValue) {
                        $option = $objectManager->create('\Magento\Catalog\Model\Product\Option')
                                    //->setProductId($_product->getId())
                                    ->setProductId($product->getData('row_id'))
                                    //->setStoreId($_product->getStoreId())
                                    ->setStoreId(0)
                                    ->addData($optionValue);
                        $option->save();
                        $product->addOption($option);
                        $product->save();
                    }*/

                    //
                    //$customOptions2 = $this->modelProductOption->getProductOptionCollection($product);

                }

                $productStockBySku = $this->stockItemRepository->getStockItemBySku($sku);
                $notify_stock_qty = $productStockBySku['notify_stock_qty'];
                if ($sku && in_array($orderItem->getProductType(), DataHelper::SUPPORTED_PRODUCT_TYPE)) {
                    $stockItemData = $this->_getStockItemData->execute($sku, $stockId);
                    $stockItemConfiguration = $this->_getStockItemConfiguration->execute($sku, $stockId);

                    $qtyLeftInStock = $stockItemData[GetStockItemDataInterface::QUANTITY]
                                    + $this->_getReservationsQuantity->execute($sku, $stockId);

                    //if ($this->helper->getQty() > $qtyLeftInStock) {
                    if ($notify_stock_qty >= $qtyLeftInStock) {
                        $orderItem->setData('saleable', $qtyLeftInStock);
                        $orderItem->setData('quantity', (int)$stockItemData[GetStockItemDataInterface::QUANTITY]);
                        $orderItem->setData('stockId', $stockId);
                        $orderItem->setData('stockName', $stockName);
                        $lowStockItems[] = $orderItem;
                    }
                }
            }

            if (!empty($lowStockItems)) {
                $this->helper->notify($lowStockItems);
            }
        }

        return $order;
    }
}

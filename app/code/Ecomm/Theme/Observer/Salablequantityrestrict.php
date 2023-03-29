<?php

namespace Ecomm\Theme\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryReservationsApi\Model\GetReservationsQuantityInterface;

class Salablequantityrestrict implements ObserverInterface
{
    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    protected $_productRepository;

    protected $_logger;

    public function __construct(
        GetStockItemDataInterface $getStockItemData,
        ProductSalableResultInterfaceFactory $productSalableResultFactory,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetReservationsQuantityInterface $getReservationsQuantity,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->getStockItemData = $getStockItemData;
        $this->productSalableResultFactory = $productSalableResultFactory;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getReservationsQuantity = $getReservationsQuantity;
        $this->_request = $request;
        $this->_productRepository = $productRepository;
        $this->_logger          = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {

        $_product = $observer->getProduct();  // you will get product object
        $requestedQty = $this->_request->getPost()->qty;
        $options = $this->_request->getPost()->options;


        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $log = $objectManager->get('\Psr\Log\LoggerInterface');
        // $level = 'ERROR';
        // $log->debug($level,$observerData);


        /*$product_stock = $_product->getExtensionAttributes()->getStockItem();

        $stockItemData = $this->getStockItemData->execute($_product->getSku(), $product_stock->getStockId());

        // @var StockItemConfigurationInterface $stockItemConfiguration 
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($_product->getSku(), $product_stock->getStockId());

        $qtyWithReservation = $stockItemData[GetStockItemDataInterface::QUANTITY] +
            $this->getReservationsQuantity->execute($_product->getSku(), $product_stock->getStockId());
        $qtyLeftInStock = $qtyWithReservation - $stockItemConfiguration->getMinQty() - $requestedQty;
        $isEnoughQty = (bool)$stockItemData[GetStockItemDataInterface::IS_SALABLE] && $qtyLeftInStock >= 0;
        // print_r($isEnoughQty);die();
        if ($isEnoughQty == '' && $options == '') {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'is_salable_with_reservations-not_enough_qty',
                    'message' => __('The requested QTY for "%1" exceeds inventory. only %2 qty available for this product.', $productName, $availableProductQty)
                ])
            ];

            return $this->productSalableResultFactory->create(['errors' => $errors]);
        }*/
    }

}
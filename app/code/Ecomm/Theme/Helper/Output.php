<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ecomm\Theme\Helper;

use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class Output extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var GetSalableQuantityDataBySku
     */
    private $getSalableQuantityDataBySku;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigInterface;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var Option
     */
    private $option;

    /**
     * @var StockItemRepository
     */
    protected $_stockItemRepository;

    /**
     * @var DateTime
     */protected $_date;

    /**
     * @var TimezoneInterface
     */protected $timezone;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param Product $product
     * @param Option $option
     * @param StockItemRepository $stockItemRepository
     * @param DateTime $date
     * @param TimezoneInterface $timezone
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        ScopeConfigInterface $scopeConfigInterface,
        Product $product,
        Option $option,
        // \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistryInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        PriceHelper $priceHelper
    ) {
        parent::__construct($context);
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->product = $product;
        $this->option = $option;
        // $this->_stockItemRepository = $stockItemRepository;
        $this->stockRegistryInterface = $stockRegistryInterface;
        $this->_date                 = $date;
        $this->timezone             = $timezone;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Return customer status
     *
     * @return string
     */
    public function getFormatedPrice($amount)
    {
        return $this->priceHelper->currency($amount, true, false);
    }

    public function getStockItem($productId)
    {
        // return $this->_stockItemRepository->get($productId);
        return $this->stockRegistryInterface->getStockItem($productId);
    }

    public function getSalableStockItem($productSku)
    {
        $saleable_qty = 0;
        $salable_quantities = $this->getSalableQuantityDataBySku->execute($productSku);

        if(!isset($salable_quantities[0]) || $salable_quantities[0]['qty'] <= 0){
            $saleable_qty = 0;
        } else {
            $saleable_qty = $salable_quantities[0]['qty'];
        }
        return $saleable_qty;
    }

    /**
     * $excludeAttr is optional array of attribute codes to
     * exclude them from additional data array
     *
     * @return array
     */
    public function getAdditionalData($_product)
    {
        $data = [];
        $attributes = $_product->getAttributes();
        foreach ($attributes as $attribute) {
            $value = $attribute->getFrontend()->getValue($_product);

            if ($value instanceof Phrase) {
                $value = (string)$value;
            }

            if (is_string($value) && strlen($value)) {
                $data[$attribute->getAttributeCode()] = [
                    'value' => $value,
                    'code' => $attribute->getAttributeCode(),
                ];
            }
        }
        return $data;
    }

    public function getConfigVal($valpath)
    {
        $configobj = $this->scopeConfigInterface->getValue($valpath);
        return $configobj;
    }


    public function getDefaultOutOfStockVal()
    {
        $configobj = $this->scopeConfigInterface->getValue('cataloginventory/item_options/min_qty');
        return $configobj;
    }


    public function getStockCheck($productSku,$productId)
    {
        $stock=[];
        $instock='1';
        $custominstock='1';

        $config_min_qty=$this->getDefaultOutOfStockVal();

        /*Checking the whether instock or out of stock*/
        $saleableqty = $this->getSalableQuantityDataBySku->execute($productSku);

         //Checking with the product qty whether it is less than/equal to the Out of Stock Threshold value
        if(!isset($saleableqty[0]) || $saleableqty[0]['qty'] <= $config_min_qty){
            $instock='0';
        }

        $product = $this->product->load($productId);
        $productstock=$this->getStockItem($productId);

       // echo "<br>Quantity".$productstock->getQty()." --- Saleable qty".$saleableqty[0]['qty'];

        if($productstock->getQty() <= $config_min_qty){
            $instock='0';
        }

        $date_now = $this->timezone->date($this->_date->date('Y-m-d H:i:s'))->format('Y-m-d');

        $shortdatedqty=[];
        $customOptions = $this->option->getProductOptionCollection($product);
        foreach($customOptions as $optionKey => $optionVal){
            if($optionVal->getValues()){
                foreach($optionVal->getValues() as $valuesKey => $valuesVal) {
                    if($valuesVal->getQuantity()<= $config_min_qty){
                        $shortdatedqty[]=0;
                    } else {
                        //Checking if the shortdated product is expired then the product will be out of stock
                        $expiry_date  = $valuesVal->getExpiryDate();
                        if ($date_now > $expiry_date){
                            $shortdatedqty[]=0;
                        } else {
                            $shortdatedqty[]=1;
                        }
                    }


                }
            }
        }
        if(count(array_keys($shortdatedqty, '0')) == count($shortdatedqty))
            $custominstock='0';

        $stock['instock']=$instock;
        $stock['custominstock']=$custominstock;

        return $stock;
    }
}

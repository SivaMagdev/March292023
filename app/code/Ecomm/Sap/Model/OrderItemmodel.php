<?php

namespace Ecomm\Sap\Model;

class OrderItemmodel extends \Magento\Framework\Model\AbstractModel implements
    \Ecomm\Sap\Api\Data\OrderItemdataInterface
{
    const KEY_ITEM_SKU = 'ITEM_SKU';

    const KEY_BATCH_NUMBER = 'BATCH_NUMBER';

    const KEY_ITEM_NAME = 'ITEM_NAME';

    const KEY_ITEM_QTY = 'ITEM_QTY';

    const KEY_UNIT_OF_MEASURE = 'UNIT_OF_MEASURE';

    const KEY_ITEM_AMOUNT = 'ITEM_AMOUNT';

    const KEY_DISCOUNT_AMOUNT = 'DISCOUNT_AMOUNT';

    const KEY_ITEM_NET_AMOUNT = 'ITEM_NET_AMOUNT';


     public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getItemSku()
    {
        return $this->_getData(self::KEY_ITEM_SKU);
    }

    /**
     * Set item_sku
     *
     * @param string $item_sku
     * @return $this
     */
    public function setItemSku($item_sku)
    {
        return $this->setData(self::KEY_ITEM_SKU, $item_sku);
    }

    public function getBatchNumber()
    {
        return $this->_getData(self::KEY_BATCH_NUMBER);
    }

    /**
     * Set batch_number
     *
     * @param string $batch_number
     * @return $this
     */
    public function setBatchNumber($batch_number)
    {
        return $this->setData(self::KEY_BATCH_NUMBER, $batch_number);
    }

    public function getItemName()
    {
        return $this->_getData(self::KEY_ITEM_NAME);
    }

    /**
     * Set item_name
     *
     * @param string $item_name
     * @return $this
     */
    public function setItemName($item_name)
    {
        return $this->setData(self::KEY_ITEM_NAME, $item_name);
    }


    public function getItemQty()
    {
        return $this->_getData(self::KEY_ITEM_QTY);
    }

    /**
     * Set item_qty
     *
     * @param string $item_qty
     * @return $this
     */
    public function setItemQty($item_qty)
    {
        return $this->setData(self::KEY_ITEM_QTY, $item_qty);
    }

    public function getUnitOfMeasure()
    {
        return $this->_getData(self::KEY_UNIT_OF_MEASURE);
    }

    /**
     * Set unit_of_measure
     *
     * @param string $unit_of_measure
     * @return $this
     */
    public function setUnitOfMeasure($unit_of_measure)
    {
        return $this->setData(self::KEY_UNIT_OF_MEASURE, $unit_of_measure);
    }


    public function getItemAmount()
    {
        return $this->_getData(self::KEY_ITEM_AMOUNT);
    }

    /**
     * Set item_amount
     *
     * @param string $item_amount
     * @return $this
     */
    public function setItemAmount($item_amount)
    {
        return $this->setData(self::KEY_ITEM_AMOUNT, $item_amount);
    }


    public function getDiscountAmount()
    {
        return $this->_getData(self::KEY_DISCOUNT_AMOUNT);
    }

    /**
     * Set discount_amount
     *
     * @param string $discount_amount
     * @return $this
     */
    public function setDiscountAmount($discount_amount)
    {
        return $this->setData(self::KEY_DISCOUNT_AMOUNT, $discount_amount);
    }


    public function getItemNetAmount()
    {
        return $this->_getData(self::KEY_ITEM_NET_AMOUNT);
    }

    /**
     * Set item_net_amount
     *
     * @param string $item_net_amount
     * @return $this
     */
    public function setItemNetAmount($item_net_amount)
    {
        return $this->setData(self::KEY_ITEM_NET_AMOUNT, $item_net_amount);
    }
}
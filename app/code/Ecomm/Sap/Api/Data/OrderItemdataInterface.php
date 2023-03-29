<?php

namespace Ecomm\Sap\Api\Data;

/**
 * @api
 */
interface OrderItemdataInterface
{
    /**
     * Get item_sku
     *
     * @return string
     */
    public function getItemSku();

      /**
     * Set item_sku
     *
     * @param string $item_sku
     * @return $this
     */
    public function setItemSku($item_sku);

    /**
     * Get batch_number
     *
     * @return string
     */
    public function getBatchNumber();

      /**
     * Set batch_number
     *
     * @param string $batch_number
     * @return $this
     */
    public function setBatchNumber($batch_number);

    /**
     * Get item_name
     *
     * @return string
     */
    public function getItemName();

      /**
     * Set item_name
     *
     * @param string $item_name
     * @return $this
     */
    public function setItemName($item_name);

    /**
     * Get item_qty
     *
     * @return string
     */
    public function getItemQty();

      /**
     * Set item_qty
     *
     * @param string $item_qty
     * @return $this
     */
    public function setItemQty($item_qty);

    /**
     * Get unit_of_measure
     *
     * @return string
     */
    public function getUnitOfMeasure();

      /**
     * Set unit_of_measure
     *
     * @param string $unit_of_measure
     * @return $this
     */
    public function setUnitOfMeasure($unit_of_measure);

    /**
     * Get item_amount
     *
     * @return string
     */
    public function getItemAmount();

      /**
     * Set item_amount
     *
     * @param string $item_amount
     * @return $this
     */
    public function setItemAmount($item_amount);

    /**
     * Get discount_amount
     *
     * @return string
     */
    public function getDiscountAmount();

      /**
     * Set discount_amount
     *
     * @param string $discount_amount
     * @return $this
     */
    public function setDiscountAmount($discount_amount);

    /**
     * Get item_net_amount
     *
     * @return string
     */
    public function getItemNetAmount();

      /**
     * Set item_net_amount
     *
     * @param string $item_net_amount
     * @return $this
     */
    public function setItemNetAmount($item_net_amount);
}
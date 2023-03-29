<?php

namespace Ecomm\Sap\Api\Data;

/**
 * @api
 */
interface SalesorderdataInterface
{

    /**
     * Get batch_id
     *
     * @return string
     */
    public function getBatchId();

      /**
     * Set batch_id
     *
     * @param string $batch_id
     * @return $this
     */
    public function setBatchId($batch_id);

    /**
     * Get orders_info
     *
     * @return \Ecomm\Sap\Api\Data\OrderdataInterface[]
     */
    public function getOrders();

      /**
     * Set orders_info
     *
     * @param string[] $orders_info
     * @return $this
     */
    public function setOrders($orders_info);

}
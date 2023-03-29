<?php

namespace Ecomm\Sap\Api;

use Ecomm\Sap\Api\Data\ProductStockOutdataInterface;

interface ProductStockOutInterface
{
    /**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return \Ecomm\Sap\Api\Data\ProductStockOutdataInterface
     */
    public function getStockDetails();
}
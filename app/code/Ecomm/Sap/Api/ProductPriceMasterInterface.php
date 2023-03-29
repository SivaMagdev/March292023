<?php

namespace Ecomm\Sap\Api;

use Ecomm\Sap\Api\Data\ProductPriceMasterdataInterface;

interface ProductPriceMasterInterface
{
    /**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return \Ecomm\Sap\Api\Data\ProductPriceMasterdataInterface
     */
    public function getPriceDetails();
}
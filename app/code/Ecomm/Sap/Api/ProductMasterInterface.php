<?php

namespace Ecomm\Sap\Api;

use Ecomm\Sap\Api\Data\ProductMasterdataInterface;

interface ProductMasterInterface
{
    /**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return \Ecomm\Sap\Api\Data\ProductMasterdataInterface
     */
    public function getArticleDetails();
}
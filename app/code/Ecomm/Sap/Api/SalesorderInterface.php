<?php

namespace Ecomm\Sap\Api;

use Ecomm\Sap\Api\Data\SalesorderdataInterface;

interface SalesorderInterface
{
    /**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return \Ecomm\Sap\Api\Data\SalesorderdataInterface
     */
    public function getOrderDetails();
}

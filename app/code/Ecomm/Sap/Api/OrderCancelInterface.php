<?php

namespace Ecomm\Sap\Api;

interface OrderCancelInterface
{
    /**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return string
     */
    public function cancelOrder();
}
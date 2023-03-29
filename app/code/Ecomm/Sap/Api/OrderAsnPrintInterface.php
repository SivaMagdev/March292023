<?php

namespace Ecomm\Sap\Api;

interface OrderAsnPrintInterface
{
    /**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return string
     */
    public function updateAsn();
}
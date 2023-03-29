<?php

namespace Ecomm\Api\Api;

use Ecomm\Api\Api\Data\ProductAttributesItemsdataInterface;

interface ProductAttributesInterface
{
    /**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return \Ecomm\Api\Api\Data\ProductAttributesItemsdataInterface
     */
    public function getAttributes();
}
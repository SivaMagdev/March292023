<?php

namespace Ecomm\Sap\Api;

use Ecomm\Sap\Api\Data\AccessTokendataInterface;

interface AccessTokenInterface
{
	/**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return \Ecomm\Sap\Api\Data\AccessTokendataInterface
     */
    public function getAccessToken();
}
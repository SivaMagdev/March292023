<?php

namespace Ecomm\Api\Api;

use Ecomm\Api\Api\Data\AccessTokendataInterface;

interface AccessTokenInterface
{
	/**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return \Ecomm\Api\Api\Data\AccessTokendataInterface
     */
    public function getAccessToken();
}
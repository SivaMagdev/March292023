<?php

namespace Ecomm\Api\Api;

use Ecomm\Api\Api\Data\VersionInfodataInterface;

interface VersionInfoInterface
{
	/**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return \Ecomm\Api\Api\Data\VersionInfodataInterface
     */
    public function getVersionInfo();
}
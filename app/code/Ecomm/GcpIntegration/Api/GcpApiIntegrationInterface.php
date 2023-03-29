<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecomm\GcpIntegration\Api;

/**
 * Product CRUD interface.
 * @api
 */
interface GcpApiIntegrationInterface
{
    /**
     * get the price details via GCP
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return string
     */
    public function getPayload();
}

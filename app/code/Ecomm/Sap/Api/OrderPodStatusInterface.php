<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecomm\Sap\Api;

/**
 * Order CRUD interface.
 * @api
 */
interface OrderPodStatusInterface
{
    /**
     * get the tracking information from SAP
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return string
     */
    public function getPayload();
}

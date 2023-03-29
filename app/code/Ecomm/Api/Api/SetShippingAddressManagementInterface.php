<?php

declare(strict_types=1);

namespace Ecomm\Api\Api;

interface SetShippingAddressManagementInterface
{

    /**
     * POST for SetShippingAddress api
     * @param string $addressId
     * @return string
     */
    public function setCustomerShippingAddress($addressId);
}


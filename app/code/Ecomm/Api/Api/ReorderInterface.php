<?php

namespace Ecomm\Api\Api;

interface ReorderInterface
{
    /**
     * 
     *
     * @api
     * @param int $cartId
     * @param int $orderId
     * @return boolean
     */
    public function createReorder($cartId,$orderId);

}

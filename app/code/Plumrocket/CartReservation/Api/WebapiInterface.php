<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\CartReservation\Api;

/**
 * @since 2.3.0
 */
interface WebapiInterface
{

    /**
     * @param string|null    $mode
     * @param integer[]|null $productIds
     * @param mixed          $customerGroupId
     * @return string
     */
    public function loadTimer($mode = null, $productIds = null, $customerGroupId = 0): string;

    /**
     * @return string
     */
    public function removeItem(): string;
}

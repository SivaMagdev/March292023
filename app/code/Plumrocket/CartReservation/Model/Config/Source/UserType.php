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
 * @package     Plumrocket Cart Reservation v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\CartReservation\Model\Config\Source;

class UserType extends AbstractSource
{
    /**
     * Option values
     */
    const ALL_USERS = 1;
    const REGISTERED_ONLY = 2;

    /**
     * {@inheritdoc}
     */
    public function toOptionHash()
    {
        return [
            self::ALL_USERS       => __('All users (registered customers & guests)'),
            self::REGISTERED_ONLY => __('Registered customers only'),
        ];
    }
}

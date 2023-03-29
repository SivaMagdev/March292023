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

class TimerMode extends AbstractSource
{
    /**
     * Option values
     */
    const SINGLE = 1;
    const SEPARATE = 2;

    /**
     * {@inheritdoc}
     */
    public function toOptionHash()
    {
        return [
            self::SINGLE   => __('Global timer for shopping cart & checkout'),
            self::SEPARATE => __('Separate timers for shopping cart & checkout'),
        ];
    }
}

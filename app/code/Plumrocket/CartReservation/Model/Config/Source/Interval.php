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
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\CartReservation\Model\Config\Source;

/**
 * @since 2.3.0
 */
class Interval extends AbstractSource
{
    /**
     * {@inheritdoc}
     */
    public function toOptionHash()
    {
        return [
            5 => '5 sec',
            6 => '6 sec',
            7 => '7 sec',
            8 => '8 sec',
            9 => '9 sec',
            10 => '10 sec',
            15 => '15 sec',
            20 => '20 sec',
            25 => '25 sec',
            30 => '30 sec',
            45 => '45 sec',
            60 => '1 min',
            90 => '1 min 30 sec',
            120 => '2 min',
            180 => '3 min',
        ];
    }
}

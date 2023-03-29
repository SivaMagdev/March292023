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

class TimerType extends AbstractSource
{
    /**
     * Option values
     */
    const TYPE_GLOBAL = 1;
    const TYPE_SEPARATE = 2;

    /**
     * {@inheritdoc}
     */
    public function toOptionHash()
    {
        return [
            self::TYPE_GLOBAL    => __('Global timer for all products in cart'),
            self::TYPE_SEPARATE  => __('Separate timer for each product in cart'),
        ];
    }
}

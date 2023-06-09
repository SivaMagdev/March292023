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

class EndAction extends AbstractSource
{
    /**
     * Option values
     */
    const REMOVE_ITEM = 1;
    const KEEP_ITEM = 2;

    /**
     * {@inheritdoc}
     */
    public function toOptionHash()
    {
        return [
            self::REMOVE_ITEM    => __('Remove product(s) from cart'),
            self::KEEP_ITEM      => __('Keep product(s) in cart'),
        ];
    }
}

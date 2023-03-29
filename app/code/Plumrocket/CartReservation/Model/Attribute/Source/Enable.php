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

namespace Plumrocket\CartReservation\Model\Attribute\Source;

class Enable extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Option values
     */
    const INHERITED = 2;
    const YES = 1;
    const NO = 0;

    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        return [
            [
                'label' => __('Inherited'),
                'value' => self::INHERITED,
            ],
            [
                'label' => __('Enabled'),
                'value' => self::YES,
            ],
            [
                'label' => __('Disabled'),
                'value' => self::NO,
            ]
        ];
    }
}

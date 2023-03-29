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
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\CartReservation\Model\Config\Backend;

class TagReplace extends \Magento\Framework\App\Config\Value
{
    /**
     * Retrieve config value
     *
     * @return string|null
     */
    public function getValue()
    {
        $value = $this->getData('value');

        if ($value) {
            $value = preg_replace('/(<\/?)(?!br)\w*[^\s>\/]/m', '$1span', $value);
        }

        return $value;
    }
}

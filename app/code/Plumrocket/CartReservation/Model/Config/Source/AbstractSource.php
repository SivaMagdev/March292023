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

use Magento\Framework\Data\CollectionDataSourceInterface;
use Magento\Framework\Option\ArrayInterface;

abstract class AbstractSource implements ArrayInterface, CollectionDataSourceInterface
{
    /**
     * @var null|array
     */
    protected $options = null;

    /**
     * Get options in array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $values = $this->toOptionHash();
            $this->options = [];

            foreach ($values as $key => $value) {
                $this->options[] = [
                    'value'    => $key,
                    'label'    => $value,
                ];
            }
        }

        return $this->options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toOptionHash()
    {
        $options = $this->toOptionArray();
        $result = [];

        foreach ($options as $option) {
            $result[ $option['value'] ] = $option['label'];
        }
        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->toOptionHash();
    }

    /**
     * Get option by key
     *
     * @return mixed
     */
    public function getByKey($key)
    {
        $data = $this->toArray();
        if (isset($data[$key])) {
            return $data[$key];
        }
    }
}

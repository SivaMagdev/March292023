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

namespace Plumrocket\CartReservation\Plugin\Adminhtml;

class OrderCreateGridProduct
{
    /**
     * @var \Plumrocket\CartReservation\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Product
     */
    protected $productHelper;

    /**
     * @param \Plumrocket\CartReservation\Helper\Data    $dataHelper
     * @param \Plumrocket\CartReservation\Helper\Product $productHelper
     */
    public function __construct(
        \Plumrocket\CartReservation\Helper\Data $dataHelper,
        \Plumrocket\CartReservation\Helper\Product $productHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->productHelper = $productHelper;
    }

    /**
     * Add info about reservation
     *
     * @param  object   $subject
     * @param  callable $proceed
     * @param  object   $row
     * @return string
     */
    public function aroundRender($subject, callable $proceed, $row)
    {
        $html = $proceed($row);

        if (! $this->dataHelper->moduleEnabled()) {
            return $html;
        }

        if ($reservations = $this->productHelper->getReservations($row->getId())) {
            $html = '<span class="prcr-reserved-label" style="color: #d20000; margin-right: 5px;">('
                . __('Reserved qty: %1', round($reservations[$row->getId()]['reserved_qty']))
                . ')</span> '
                . $html;
        }

        return $html;
    }
}

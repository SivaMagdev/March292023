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

namespace Plumrocket\CartReservation\Plugin;

use Magento\GroupedProduct\Model\Product\Type\Grouped;

class AbstractProduct
{
    /**
     * @var \Plumrocket\CartReservation\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Plumrocket\CartReservation\Block\TimerFactory
     */
    protected $timerFactory;

    /**
     * @param \Plumrocket\CartReservation\Helper\Data        $dataHelper
     * @param \Plumrocket\CartReservation\Helper\Config      $configHelper
     * @param \Plumrocket\CartReservation\Helper\Product     $productHelper
     * @param \Plumrocket\CartReservation\Block\TimerFactory $timerFactory
     */
    public function __construct(
        \Plumrocket\CartReservation\Helper\Data $dataHelper,
        \Plumrocket\CartReservation\Helper\Config $configHelper,
        \Plumrocket\CartReservation\Helper\Product $productHelper,
        \Plumrocket\CartReservation\Block\TimerFactory $timerFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->productHelper = $productHelper;
        $this->timerFactory = $timerFactory;
    }

    /**
     * Add timers html to products html
     *
     * @param  object $subject
     * @param  string $html
     * @return string
     */
    public function afterToHtml($subject, $html)
    {
        if (! $this->dataHelper->moduleEnabled()) {
            return $html;
        }

        if ($products = $this->getProducts($subject)) {
            $timersHtml = [];
            foreach ($products as $product) {
                // Stop if product is not available because the button "Add to cart" is missing.
                if (! $product->isAvailable()) {
                    continue;
                }

                $timer = $this->timerFactory->create()
                    ->setCountdownLayout($this->configHelper->getTimerFormatOnProduct())
                    ->setProductIds($this->productHelper->getAllIds($product))
                    ->setDataAttr('on-init-after', 'prcrGridProductReservedStatus')
                    ->addClass('prcr_product_' . strtolower($product->getTypeId()));

                if ($product->getTypeId() == Grouped::TYPE_CODE) {
                    $timer->setDataAttr('on-init-before', 'prcrGroupedProductInit');
                }

                $timersHtml[] = $timer->toHtml();
            }

            $html = preg_replace_callback(
                '#<button.+?class="[^"]*?tocart[^"]*?".*?>.+?</button>#uis',
                function ($matches) use (&$timersHtml) {
                    return $matches[0] . array_shift($timersHtml);
                },
                $html
            );
        }

        return $html;
    }

    /**
     * Try to get products in block
     *
     * @param  object $subject
     * @return object|null
     */
    private function getProducts($subject)
    {
        if (! $collection = $subject->getProductCollection()) {
            $collection =  $subject->getLoadedProductCollection();
        }

        return $collection;
    }
}

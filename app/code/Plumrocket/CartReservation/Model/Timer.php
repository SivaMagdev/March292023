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
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\CartReservation\Model;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Plumrocket\CartReservation\Api\TimerInterface;
use Plumrocket\CartReservation\Helper\Config;
use Plumrocket\CartReservation\Helper\Data;
use Plumrocket\CartReservation\Helper\Item as ItemHelper;
use Plumrocket\CartReservation\Helper\Product;
use Plumrocket\CartReservation\Model\Config\Source\TimerType;

/**
 * @since 2.3.0
 */
class Timer implements TimerInterface
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
     * @var \Plumrocket\CartReservation\Helper\Item
     */
    protected $itemHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @param \Plumrocket\CartReservation\Helper\Data $dataHelper
     * @param \Plumrocket\CartReservation\Helper\Config $configHelper
     * @param \Plumrocket\CartReservation\Helper\Item $itemHelper
     * @param \Plumrocket\CartReservation\Helper\Product $productHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        Data $dataHelper,
        Config $configHelper,
        ItemHelper $itemHelper,
        Product $productHelper,
        DateTime $dateTime
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->itemHelper = $itemHelper;
        $this->productHelper = $productHelper;
        $this->dateTime = $dateTime;
    }

    /**
     * @inheritDoc
     */
    public function getList($mode = null, $productIds = null): array
    {
        $data = ['success' => true];

        if (! $this->dataHelper->moduleEnabled()) {
            return $data;
        }

        // Switch mode.
        if ($mode) {
            $this->itemHelper->switchMode($mode);
        }

        // Current GMT time.
        $data['current_time'] = $this->dateTime->gmtTimestamp();

        // Get products timers.
        if (! $productIds) {
            $productIds = [];
        }

        $quoteId = $this->itemHelper->getQuoteId();
        $data['products'] = $this->productHelper->getTimers($productIds, null, $quoteId);

        $prevQuoteProductIds = $this->dataHelper->getQuoteProductIds();
        $quoteProductIds = [];
        if ($quoteId) {
            foreach ($data['products'] as $product) {
                if ($product['quote_id'] == $quoteId) {
                    $quoteProductIds[] = $product['product_id'];
                }
            }

            $this->dataHelper->setQuoteProductIds($quoteProductIds);
        }

        // Mark product timers as removed.
        $requestedProducts = array_merge($productIds, $prevQuoteProductIds);
        if ($requestedProducts) {
            $requestedProducts = array_unique($requestedProducts);
            foreach ($requestedProducts as $productId) {
                if (isset($data['products'][$productId])) {
                    continue;
                }

                $data['products'][$productId] = [
                    'product_id' => $productId,
                    'removed' => true
                ];
            }
        }

        // Add data of items in current quote.
        if ($this->configHelper->getCartReservationType() == TimerType::TYPE_SEPARATE
            && $this->dataHelper->getTimerMode() == Data::TIMER_MODE_CART
            && $this->itemHelper->getQuoteId()
        ) {
            $data['items'] = [];
            $items = $this->itemHelper->getQuoteItems();
            foreach ($items as $item) {
                if ($this->dateTime->gmtTimestamp() >= $item->getData('timer_expire_at')) {
                    continue;
                }

                $data['items'][$item->getId()] = [
                    'item_id' => $item->getId(),
                    'timer_expire_at' => $item->getData('timer_expire_at')
                ];
            }
        }

        if ($this->configHelper->getCartReservationType() == TimerType::TYPE_GLOBAL
            || $this->dataHelper->getTimerMode() == Data::TIMER_MODE_CHECKOUT
        ) {
            $globalTime = $this->itemHelper->getGlobalTime();
            if ($this->dateTime->gmtTimestamp() >= $globalTime) {
                $globalTime = null;
            }

            $data['global_timer'] = [
                'timer_expire_at' => $globalTime
            ];
        }

        return $data;
    }
}

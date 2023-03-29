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

namespace Plumrocket\CartReservation\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Plumrocket\CartReservation\Api\TimerInterface;
use Plumrocket\CartReservation\Helper\Data;

/**
 * @since 2.3.0
 */
class Timer implements SectionSourceInterface
{
    /**
     * @var \Plumrocket\CartReservation\Api\TimerInterface
     */
    private $timer;

    /**
     * @var \Plumrocket\CartReservation\Helper\Data
     */
    private $dataHelper;

    /**
     * @param \Plumrocket\CartReservation\Api\TimerInterface $timer
     * @param \Plumrocket\CartReservation\Helper\Data $dataHelper
     */
    public function __construct(
        TimerInterface $timer,
        Data $dataHelper
    ) {
        $this->timer = $timer;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @inheritDoc
     */
    public function getSectionData(): array
    {
        // Get product ids that customer tried to add to cart
        $productIds = $this->dataHelper->getProductIds();
        $this->dataHelper->setProductIds([]);

        return $this->timer->getList(null, $productIds);
    }
}

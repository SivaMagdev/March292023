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

namespace Plumrocket\CartReservation\Model;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Serialize\SerializerInterface;
use Plumrocket\CartReservation\Api\ItemInterface;
use Plumrocket\CartReservation\Api\TimerInterface;
use Plumrocket\CartReservation\Api\WebapiInterface;

/**
 * @since 2.3.0
 */
class Webapi implements WebapiInterface
{
    /**
     * @var \Plumrocket\CartReservation\Api\TimerInterface
     */
    private $timer;

    /**
     * @var \Plumrocket\CartReservation\Api\ItemInterface
     */
    private $item;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @param \Plumrocket\CartReservation\Api\TimerInterface   $timer
     * @param \Plumrocket\CartReservation\Api\ItemInterface    $item
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\App\Http\Context              $httpContext
     */
    public function __construct(
        TimerInterface $timer,
        ItemInterface $item,
        SerializerInterface $serializer,
        HttpContext $httpContext
    ) {
        $this->timer = $timer;
        $this->item = $item;
        $this->serializer = $serializer;
        $this->httpContext = $httpContext;
    }

    /**
     * @inheritDoc
     */
    public function loadTimer($mode = null, $productIds = null, $customerGroupId = 0): string
    {
        /**
         * As admin can set different reservation time for different customer groups
         * we have to have customer id in http context to reserve products correctly in REST mode.
         */
        $this->httpContext->setValue(CustomerContext::CONTEXT_GROUP, (int) $customerGroupId, 0);

        return $this->serializer->serialize(
            $this->timer->getList($mode, $productIds)
        );
    }

    /**
     * @inheritDoc
     */
    public function removeItem(): string
    {
        return $this->serializer->serialize(
            $this->item->remove()
        );
    }
}

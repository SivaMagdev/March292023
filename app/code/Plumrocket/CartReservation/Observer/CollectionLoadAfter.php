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

namespace Plumrocket\CartReservation\Observer;

use Magento\Framework\Event\Observer;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection as ItemCollection;
use Plumrocket\CartReservation\Helper\Data;
use Plumrocket\CartReservation\Model\Config\Source\TimerType;

class CollectionLoadAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Product
     */
    private $productHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Plumrocket\CartReservation\Block\TimerFactory
     */
    private $timerFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param Data $dataHelper
     *
     * @param \Plumrocket\CartReservation\Helper\Config $configHelper
     * @param \Plumrocket\CartReservation\Helper\Product $productHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Plumrocket\CartReservation\Block\TimerFactory $timerFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        Data $dataHelper,
        \Plumrocket\CartReservation\Helper\Config $configHelper,
        \Plumrocket\CartReservation\Helper\Product $productHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Plumrocket\CartReservation\Block\TimerFactory $timerFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->productHelper = $productHelper;
        $this->request = $request;
        $this->timerFactory = $timerFactory;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $collection = $observer->getCollection();
        if ($collection instanceof ItemCollection) {
            if (! $this->dataHelper->moduleEnabled()) {
                return;
            }

            if ($this->dataHelper->isGuestMode()) {
                return;
            }

            if ($this->configHelper->getCartReservationType() != TimerType::TYPE_SEPARATE) {
                return;
            }

            switch (true) {
                // Skip on checkout page.
                case 'checkout' === $this->request->getModuleName()
                    && 'cart' !== $this->request->getControllerName():
                    // no break
                // Skip on update product page (strangely but it affected to qty calculation).
                case 'checkout' === $this->request->getModuleName()
                    && 'cart' === $this->request->getControllerName()
                    && 'updateItemOptions' === $this->request->getActionName():
                    // no break
                // Skip if this is post request.
                case $this->request->isPost():
                    // no break
                case Data::SECTION_ID === $this->request->getModuleName():
                    return;
            }
            
            foreach ($collection as $item) {
                if (! $this->configHelper->isEnabledReservationForVirtual()
                    || false === $this->productHelper->reservationEnabled($item->getProductId())) {
                    continue;
                }

                if ($options = $this->getOptions($item)) {
                    if (! $item->getPrCrOption()) {
                        $item->setPrCrOption(true);
                        $item->addOption([
                            'code' => 'additional_options',
                            'value' => json_encode($options),
                            'product_id' => $item->getProductId(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Retrieve timer options
     *
     * @param  object $item
     * @return array
     */
    private function getOptions($item)
    {
        $options = [];

        $timerHtml = $this->timerFactory->create()
            ->setCountdownLayout($this->configHelper->getTimerFormatSeparate())
            ->setItemIds($item->getId())
            // Remove all line breaks, because they are replaced to "br" tag in product options.
            ->toHtmlOneLine();

        if (version_compare($this->dataHelper->getMagentoVersion(), '2.3.1', '>=')
            || (version_compare($this->dataHelper->getMagentoVersion(), '2.2.8', '>=')
                && version_compare($this->dataHelper->getMagentoVersion(), '2.3.0', '<'))
        ) {
            $timerHtml = [$timerHtml];
        }

        $options[] = [
            'label' => '',
            'value' => $timerHtml,
            'custom_view' => true,
        ];

        return $options;
    }
}

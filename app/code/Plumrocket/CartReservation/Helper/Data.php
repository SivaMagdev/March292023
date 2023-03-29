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

namespace Plumrocket\CartReservation\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Plumrocket\CartReservation\Model\Config\Source\UserType;

class Data extends AbstractHelper
{
    /**
     * Config section id
     */
    const SECTION_ID = 'prcr';

    /**
     * Session param name for timer mode
     */
    const TIMER_MODE_PARAM = 'prcr_timer_mode';

    /**
     * Session param name for quote product ids
     */
    const QUOTE_PRODUCT_IDS_PARAM = 'prcr_quote_product_ids';

    /**
     * Session param name for product ids
     */
    const PRODUCT_IDS_PARAM = 'prcr_product_ids';

    /**
     * Timer modes
     */
    const TIMER_MODE_CART = 'cart';
    const TIMER_MODE_CHECKOUT = 'checkout';

    /**
     * @var \Plumrocket\CartReservation\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $session;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetaData;

    /**
     * @param \Magento\Framework\App\Helper\Context              $context
     * @param \Plumrocket\CartReservation\Helper\Config          $configHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime        $dateTime
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\App\ProductMetadataInterface    $productMetaData
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Plumrocket\CartReservation\Helper\Config $configHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->dateTime = $dateTime;
        $this->session = $session;
        $this->productMetaData = $productMetaData;
    }

    /**
     * Check if is module enabled
     *
     * @param  int|null $store store id
     * @return boolean
     */
    public function moduleEnabled($store = null)
    {
        return $this->configHelper->isModuleEnabled($store);
    }

    /**
     * Collection of paths for check timer mode
     *
     * @return array
     */
    public function getCheckoutPaths()
    {
        // Ignored paths need to mark by "!" and place into start of list.
        return [
            '!checkout/cart',

            '/checkout',
            'checkout/onepage',
            'checkout/success',
            'multishipping/checkout'
        ];
    }

    /**
     * Get current mode
     *
     * @return string
     */
    public function getTimerMode()
    {
        return $this->session->getData(self::TIMER_MODE_PARAM) ?: self::TIMER_MODE_CART;
    }

    /**
     * Set current mode
     *
     * @param string $timerMode
     * @return bool
     */
    public function setTimerMode($timerMode)
    {
        $this->session->setData(self::TIMER_MODE_PARAM, $timerMode);

        return true;
    }

    /**
     * Check if guest mode is active
     *
     * @return boolean
     */
    public function isGuestMode()
    {
        return $this->configHelper->getUserType() == UserType::REGISTERED_ONLY
            && ! $this->session->isLoggedIn();
    }

    /**
     * Retrieve expire at time
     *
     * @param  int|string|array $offset Cart or checkout time from config
     * @param  bool         $inSeconds
     * @param  string       $format
     * @return int|string
     */
    public function getExpireAt($offset, $inSeconds = true, $format = 'Y-m-d H:i:s')
    {
        $now = $this->dateTime->gmtTimestamp();
        if (is_numeric($offset)) { // offset in seconds
            $time = $now + $offset;
        } else { // offset in '00,00,00,00' format or ['00','00','00','00']
            // This format is deprecated end not be supported in next versions.
            if (! is_array($offset)) {
                $offset = explode(',', $offset, 4);
            }
            [$d, $h, $i, $s] = $offset;
            $time = strtotime("+$d days $h hours $i minutes $s seconds", $now);
        }

        return $inSeconds ? $time : date($format, $time);
    }

    /**
     * @param string|array $offset
     * @return int
     */
    public function parseTime($offset): int
    {
        $now = $this->dateTime->gmtTimestamp();
        $expireAt = $this->getExpireAt($offset);

        return $expireAt - $now;
    }

    /**
     * Get quote product ids
     *
     * @return array
     */
    public function getQuoteProductIds()
    {
        return $this->session->getData(self::QUOTE_PRODUCT_IDS_PARAM) ?: [];
    }

    /**
     * Set quote product ids
     *
     * @param array $productIds
     * @return bool
     */
    public function setQuoteProductIds($productIds)
    {
        $this->session->setData(self::QUOTE_PRODUCT_IDS_PARAM, $productIds);

        return true;
    }

    /**
     * Get product ids
     *
     * @return array
     */
    public function getProductIds()
    {
        return $this->session->getData(self::PRODUCT_IDS_PARAM) ?: [];
    }

    /**
     * Set product ids
     *
     * @param array $productIds
     * @return bool
     */
    public function setProductIds($productIds)
    {
        $this->session->setData(self::PRODUCT_IDS_PARAM, $productIds);

        return true;
    }

    /**
     * Get Magento Version
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->productMetaData->getVersion();
    }
}

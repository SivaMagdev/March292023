<?php
/**
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\CartReservation\Helper;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Plumrocket\Base\Api\ConfigUtilsInterface;
use Plumrocket\Base\Helper\ConfigUtils;

class Config extends ConfigUtils
{

    /**
     * Default time to show popup reminder
     */
    private const TIME_REMINDER = '0,0,10,0';

    public const XML_PATH_IS_MODULE_ENABLED = 'prcr/general/enabled';
    public const XML_PATH_CART_RESERVATION_TIME = 'prcr/cart/time';
    public const XML_PATH_CART_RESERVATION_TIME_BY_GROUP = 'prcr/cart/customer_group_time';
    public const XML_PATH_CHECKOUT_RESERVATION_TIME = 'prcr/checkout/time';
    public const XML_PATH_CHECKOUT_RESERVATION_TIME_BY_GROUP = 'prcr/checkout/customer_group_time';
    public const XML_PATH_CHECKOUT_REMINDER_TIME = 'prcr/popup_reminder/alert_reminder_time';
    public const XML_PATH_REMINDER_POPUP_SHOW_OVERLAY = 'prcr/popup_reminder/show_overlay';

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Plumrocket\Base\Api\ConfigUtilsInterface
     */
    private $configUtils;

    /**
     * @param \Magento\Framework\App\Helper\Context            $context
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\App\Http\Context              $httpContext
     * @param \Plumrocket\Base\Api\ConfigUtilsInterface        $configUtils
     */
    public function __construct(
        Context $context,
        SerializerInterface $serializer,
        \Magento\Framework\App\Http\Context $httpContext,
        ConfigUtilsInterface $configUtils
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
        $this->httpContext = $httpContext;
        $this->configUtils = $configUtils;
    }

    /**
     * Check if module is enabled.
     *
     * @param $store
     * @param $scope
     * @return bool
     */
    public function isModuleEnabled($store = null, $scope = null): bool
    {
        return $this->configUtils->isSetFlag(self::XML_PATH_IS_MODULE_ENABLED, $store, $scope);
    }

    /**
     * Get timer mode
     *
     * @return int
     */
    public function getTimerMode()
    {
        return (int) $this->configUtils->getConfig(
            Data::SECTION_ID . '/general/timer_mode'
        );
    }

    /**
     * Get user type that can reserve an item
     *
     * @return string
     */
    public function getUserType()
    {
        return (string) $this->configUtils->getConfig(
            Data::SECTION_ID . '/general/user_type'
        );
    }

    /**
     * Check if enabled reservation for virtual products.
     *
     * @return bool
     */
    public function isEnabledReservationForVirtual(): bool
    {
        return (bool) $this->configUtils->getConfig(
            Data::SECTION_ID . '/general/reservation_for_virtual_products'
        );
    }

    /**
     * Get reservation type of cart
     *
     * @return int
     */
    public function getCartReservationType()
    {
        return (int) $this->configUtils->getConfig(
            Data::SECTION_ID . '/cart/reservation_type'
        );
    }

    /**
     * Get cart action after time ended
     *
     * @return string
     */
    public function getCartEndAction()
    {
        return (string) $this->configUtils->getConfig(
            Data::SECTION_ID . '/cart/end_action'
        );
    }

    /**
     * Get amount of seconds extension should reserve products for.
     *
     * @return int seconds
     */
    public function getCartTime(): int
    {
        return $this->getTimeInSeconds(
            $this->configUtils->getConfig(self::XML_PATH_CART_RESERVATION_TIME),
            $this->configUtils->getConfig(self::XML_PATH_CART_RESERVATION_TIME_BY_GROUP)
        );
    }

    /**
     * Get checkout action before enter
     *
     * @return string
     */
    public function getCheckoutStartAction()
    {
        return (string) $this->configUtils->getConfig(
            Data::SECTION_ID . '/checkout/start_action'
        );
    }

    /**
     * Get checkout time
     *
     * @return int
     */
    public function getCheckoutTime(): int
    {
        return $this->getTimeInSeconds(
            $this->configUtils->getConfig(self::XML_PATH_CHECKOUT_RESERVATION_TIME),
            $this->configUtils->getConfig(self::XML_PATH_CHECKOUT_RESERVATION_TIME_BY_GROUP)
        );
    }

    /**
     * @param string|null $timeConfig
     * @param string|null $timeByGroupConfig
     * @return int
     */
    private function getTimeInSeconds(?string $timeConfig, ?string $timeByGroupConfig = null): int
    {
        $timeByGroup = [];
        if ($timeByGroupConfig) {
            $decodedValue = $this->serializer->unserialize($timeByGroupConfig);
            if (is_array($decodedValue)) {
                $timeByGroup = $decodedValue;
            }
        }

        $customerGroupId = (int) $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
        foreach ($timeByGroup as $row) {
            if ($customerGroupId === (int) $row['customer_group']) {
                return $row['time'] * 60;
            }
        }

        if (! $timeConfig) {
            return 0;
        }

        /**
         * Default time has the following format - days,hours,minutes,seconds
         * To standardize output we have to convert time into seconds.
         */
        [$d, $h, $i, $s] = explode(',', $timeConfig);
        return ($d * 3600 * 24) + ($h * 3600) + ($i * 60) + $s;
    }

    /**
     * Show or hide timer on checkout
     *
     * @return string
     */
    public function displayCheckoutTimer()
    {
        return (string) $this->configUtils->getConfig(
            Data::SECTION_ID . '/checkout/timer_display'
        );
    }

    /**
     * Get global timer format
     *
     * @return string
     */
    public function getTimerFormatGlobal()
    {
        return (string) $this->configUtils->getConfig(
            Data::SECTION_ID . '/timer/format_global'
        );
    }

    /**
     * Get separate timer format
     *
     * @return string
     */
    public function getTimerFormatSeparate()
    {
        return (string) $this->configUtils->getConfig(
            Data::SECTION_ID . '/timer/format_separate'
        );
    }

    /**
     * Get timer format on product
     *
     * @return string
     */
    public function getTimerFormatOnProduct()
    {
        return (string) $this->configUtils->getConfig(
            Data::SECTION_ID . '/timer/format_on_product'
        );
    }

    /**
     * @return int
     */
    public function isAutoRefreshEnabled(): int
    {
        return (int) $this->configUtils->getConfig(
            Data::SECTION_ID . '/timer/auto_refresh_enable'
        );
    }

    /**
     * @return int
     */
    public function getAutoRefreshInterval(): int
    {
        $seconds = (int) $this->configUtils->getConfig(
            Data::SECTION_ID . '/timer/auto_refresh_interval'
        );

        return max($seconds, $this->getAutoRefreshMinInterval());
    }

    /**
     * @return int
     */
    public function getAutoRefreshMinInterval(): int
    {
        return 5;
    }

    /**
     * @return int
     */
    public function isAlertEnabled(): int
    {
        return (int) $this->configUtils->getConfig(
            Data::SECTION_ID . '/popup_reminder/reservation_alert'
        );
    }

    /**
     * Get template for reminder popup
     *
     * @return string
     */
    public function getAlertTemplate(): string
    {
        return (string) $this->configUtils->getConfig(
            Data::SECTION_ID . '/popup_reminder/alert_template'
        );
    }

    /**
     * Get time when display remind popup
     *
     * @return int
     */
    public function getAlertRemindTime(): int
    {
        return $this->getTimeInSeconds(
            $this->configUtils->getConfig(self::XML_PATH_CHECKOUT_REMINDER_TIME) ?: self::TIME_REMINDER
        );
    }

    /**
     * Get time when display remind popup
     *
     * @return bool
     */
    public function shouldShowOverlay(): bool
    {
        return $this->configUtils->isSetFlag(self::XML_PATH_REMINDER_POPUP_SHOW_OVERLAY);
    }
}

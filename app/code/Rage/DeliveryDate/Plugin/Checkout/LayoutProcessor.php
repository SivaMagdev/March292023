<?php
namespace Rage\DeliveryDate\Plugin\Checkout;

class LayoutProcessor
{
    const XPATH_STATUS   = 'rg_deliverydate/general/enable';

    public function __construct(
        \Rage\DeliveryDate\Helper\Data $helperData
    ) {
        $this->helper_data = $helperData;
    }
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        $module_status = $this->helper_data->getConfigValue(self::XPATH_STATUS);
        if (!$module_status) {
            unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shippingAdditional']);
        }
        return $jsLayout;
    }
}

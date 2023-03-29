<?php
namespace Rage\DeliveryDate\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class DeliveryDateConfigProvider implements ConfigProviderInterface
{
    const XPATH_STATUS   = 'rg_deliverydate/general/enable';
    const XPATH_IS_UNAVAILABLE_DAY = 'rg_deliverydate/general/is_unavailable_days';
    const XPATH_DISABLED = 'rg_deliverydate/general/disabled';
    const XPATH_FORMAT   = 'rg_deliverydate/general/format';
    const XPATH_DATE_HINT   = 'rg_deliverydate/general/delivery_date_hint';
    const XPATH_NOTES_HINT   = 'rg_deliverydate/general/delivery_notes_hint';

    public function __construct(
        \Rage\DeliveryDate\Helper\Data $helperData
    ) {
        $this->helper_data = $helperData;
    }

    public function getConfig()
    {
        $module_status = $this->helper_data->getConfigValue(self::XPATH_STATUS);
        $is_unavailable_day = $this->helper_data->getConfigValue(self::XPATH_IS_UNAVAILABLE_DAY);
        $disabled = $this->helper_data->getConfigValue(self::XPATH_DISABLED);
        $format = $this->helper_data->getConfigValue(self::XPATH_FORMAT);
        $date_hint = $this->helper_data->getConfigValue(self::XPATH_DATE_HINT);
        $notes_hint = $this->helper_data->getConfigValue(self::XPATH_NOTES_HINT);

        $config = [
            'shipping' => [
                'rgdd_delivery_date' => [
                    'status' => $module_status,
                    'is_unavailable_day'=>$is_unavailable_day,
                    'format' => $format,
                    'disabled' => $disabled,
                    'date_hint' => $date_hint,
                    'notes_hint' => $notes_hint
                ]
            ]
        ];
        return $config;
    }
}

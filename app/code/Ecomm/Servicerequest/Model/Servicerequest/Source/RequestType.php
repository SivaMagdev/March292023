<?php

namespace Ecomm\Servicerequest\Model\Servicerequest\Source;

class RequestType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        //$options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    public static function getOptionArray()
    {
        return [
            1 => 'General Inquiries',
            2 => 'Product Complaints',
            3 => 'Product Inquiries',
            4 => 'Upcoming Order',
            5 => 'Returns / Cancellation',
            6 => 'Damage / Shortages',
            7 => 'Profile Update'
        ];
    }
}
?>
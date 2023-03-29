<?php

namespace Ecomm\Theme\Model\Addressbook\Source;

class addressstatus implements \Magento\Framework\Data\OptionSourceInterface
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
            49 => 'Pending',
            50 => 'Approved',
            51 => 'Rejected'
        ];
    }
}
?>
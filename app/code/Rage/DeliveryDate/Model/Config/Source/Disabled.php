<?php
namespace Rage\DeliveryDate\Model\Config\Source;

class Disabled implements \Magento\Framework\Option\ArrayInterface
{
    public $localeLists;

    public function __construct(\Magento\Framework\Locale\ListsInterface $localeLists)
    {
        $this->localeLists = $localeLists;
    }

    public function toOptionArray()
    {
        $options = $this->localeLists->getOptionWeekdays();
        return $options;
    }
}

<?php
namespace Ecomm\ExclusivePrice\Model\ContractPrice;
use Magento\Framework\Data\OptionSourceInterface;
class Status implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => 'No', 'value' => 0];
        $options[] = ['label' => 'Yes', 'value' => 1];
        return $options;
    }
}
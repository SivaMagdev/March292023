<?php
namespace Ecomm\Globaldeclaration\Model\Config\Source;

class PriceNotifyMonthsDay implements \Magento\Framework\Data\OptionSourceInterface
{
	public function toOptionArray()
	{
		return [
			['value' => '90', 	'label' => __('90 Days')],
			['value' => '180', 	'label' => __('180 Days')],
			['value' => '270', 	'label' => __('270 Days')],
			['value' => '365', 	'label' => __('365 Days')]
		];
	}
}
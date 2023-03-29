<?php
/**
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\CartReservation\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroup;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Plumrocket\CartReservation\Block\Adminhtml\System\Config\Form\DatePeriod\InputTable;
use Plumrocket\CartReservation\Block\Adminhtml\System\Config\Form\Renderer\Button;

/**
 * @since 2.4.0
 */
class DatePeriod extends Field
{

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    private $customerGroup;

    /**
     * @param \Magento\Backend\Block\Template\Context                $context
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
     * @param array                                                  $data
     */
    public function __construct(
        Context $context,
        CustomerGroup $customerGroup,
        array $data = []
    ) {
        $this->customerGroup = $customerGroup;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $customerGroups = [];
        foreach ($this->customerGroup->toOptionArray() as $item) {
            $customerGroups[$item['value']] = $item['label'];
        }

        $html = '<input type="hidden" name="' . $element->getname() . '" value="" />';

        /** @var \Plumrocket\CartReservation\Block\Adminhtml\System\Config\Form\DatePeriod\InputTable $table */
        $table = $this->getLayout()->createBlock(InputTable::class);
        $table
            ->setConatinerFieldId($element->getName())
            ->setRowKey('name')
            ->addColumn('customer_group', [
                'header'           => __('Customer Group'),
                'index'            => 'customer_group',
                'type'             => 'select',
                'options'          => $customerGroups,
                'value'            => 2,
                'column_css_class' => 'customer-group',
            ])
            ->addColumn('time', [
                'header'           => __('Time (Minutes)'),
                'index'            => 'time',
                'type'             => 'input',
                'value'            => '',
                'column_css_class' => 'time',
            ])
            ->addColumn('remove', [
                'header'           => __('Action'),
                'index'            => 'remove',
                'type'             => 'text',
                'renderer'         => Button::class,
                'value'            => 1,
                'column_css_class' => 'remove',
            ])
            ->setArray($this->_getValue($element->getValue()));

        /** @var \Magento\Backend\Block\Widget\Button $button */
        $button = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class);
        $button->addData(
            [
                'label' => __('Add Row'),
                'type'  => 'button',
                'class' => 'add dateperiod-add',
            ]
        );

        return $html . $table->toHtml() . $button->toHtml();
    }

    protected function _getValue($data = []): array
    {
        $rows = [
            '_TMPNAME_' => [],
        ];

        if ($data && is_array($data)) {
            $rows = array_merge($rows, $data);
        }

        foreach ($rows as $name => &$row) {
            $row = array_merge($row, ['name' => $name]);
        }

        return $rows;
    }
}

<?php
/**
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\CartReservation\Block\Adminhtml\System\Config\Form\DatePeriod\InputTable;

use Magento\Backend\Block\Widget\Grid\Column\Extended;
use Magento\Framework\DataObject;

/**
 * @since 2.4.0
 */
class Column extends Extended
{
    protected $_rowKeyValue = null;

    public function getId()
    {
        return preg_replace('#-$#', '', $this->getName());
    }

    public function getRowField(DataObject $row)
    {
        if (null !== $this->getGrid()->getRowKey()) {
            $this->_rowKeyValue = $row->getData($this->getGrid()->getRowKey());
        }

        if (! $this->_rowKeyValue) {
            return '';
        }

        return str_replace(' type="text"', ' type="number" min="0"', parent::getRowField($row));
    }

    public function getFieldName()
    {
        return $this->getName();
    }

    public function getHtmlName()
    {
        return $this->getName();
    }

    public function getName()
    {
        return sprintf(
            '%s[%s][%s]',
            $this->getGrid()->getContainerFieldId(),
            $this->_rowKeyValue,
            parent::getId()
        );
    }
}

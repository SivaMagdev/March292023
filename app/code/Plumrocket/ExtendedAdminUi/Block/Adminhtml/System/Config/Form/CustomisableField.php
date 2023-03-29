<?php
/**
 * @package     Plumrocket_ExtendedAdminUi
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Frontend model for select field
 *
 * See README.md for instruction
 *
 * @since 1.0.4
 */
class CustomisableField extends Field
{
    /**
     * Render element value
     *
     * @param \Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\Element\ImageRadioButtons $element
     * @return string
     */
    protected function _renderValue(AbstractElement $element): string
    {
        $valueCssClass = $element->getPrValueCustomCssClass();

        if ($element->getTooltip()) {
            $html = '<td class="value with-tooltip ' . $valueCssClass . '">';
            $html .= $this->_getElementHtml($element);
            $html .= '<div class="tooltip"><span class="help"><span></span></span>';
            $html .= '<div class="tooltip-content">' . $element->getTooltip() . '</div></div>';
        } else {
            $html = '<td class="value ' . $valueCssClass . '">';
            $html .= $this->_getElementHtml($element);
        }
        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .= '</td>';
        return $html;
    }

    /**
     * Disable checkbox if needed.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return bool
     */
    protected function _isInheritCheckboxRequired(AbstractElement $element): bool
    {
        return (! $element->getPrDisableInheritCheckbox()) && parent::_isInheritCheckboxRequired($element);
    }
}

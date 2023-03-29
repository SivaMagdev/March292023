<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Cart Reservation v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\CartReservation\Block\Adminhtml\System\Config\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Time extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);

        $dayValue = 0;
        $hourValue = 0;
        $minValue = 0;
        $secValue = 0;

        if ($value = $element->getValue()) {
            $values = explode(',', $value);
            if (is_array($values) && count($values) == 4) {
                list ($dayValue, $hourValue, $minValue, $secValue) = $values;
            }
        }

        $html = '<input type="hidden" id="' . $element->getHtmlId() . '" />';

        $html .= '<select name="' . $element->getName() . '" ' . $element->serialize($element->getHtmlAttributes()) . ' style="width:40px">' . PHP_EOL;
        foreach (range(0, 89) as $day) {
            $day = str_pad($day, 2, '0', STR_PAD_LEFT);
            $html .= '<option value="' . $day . '" ' . ($dayValue == $day ? 'selected="selected"' : '') . '>' . $day . '</option>';
        }
        $html .= '</select>&nbsp;&nbsp;' . PHP_EOL;

        $html .= '<select name="'. $element->getName() . '" '.$element->serialize($element->getHtmlAttributes()).' style="width:40px">' . PHP_EOL;
        foreach (range(0, 23) as $hour) {
            $hour = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $html .= '<option value="' . $hour . '" ' . ($hourValue == $hour ? 'selected="selected"' : '') . '>' . $hour . '</option>';
        }
        $html .= '</select>&nbsp;:&nbsp;' . PHP_EOL;

        $html .= '<select name="' . $element->getName() . '" ' .$element->serialize($element->getHtmlAttributes()) . ' style="width:40px">' . PHP_EOL;
        foreach (range(0, 59) as $min) {
            $min = str_pad($min, 2, '0', STR_PAD_LEFT);
            $html .= '<option value="' . $min . '" ' . ($minValue == $min ? 'selected="selected"' : '') . '>' . $min . '</option>';
        }
        $html .= '</select>&nbsp;:&nbsp;' . PHP_EOL;

        $html .= '<select name="' . $element->getName() . '" ' . $element->serialize($element->getHtmlAttributes()) . ' style="width:40px">' . PHP_EOL;
        foreach (range(0, 59) as $sec) {
            $sec = str_pad($sec, 2, '0', STR_PAD_LEFT);
            $html .= '<option value="' . $sec . '" ' . ($secValue == $sec ? 'selected="selected"' : '') . '>' . $sec . '</option>';
        }
        $html .= '</select>' . PHP_EOL;

        $html .= $element->getAfterElementHtml();

        return $html;
    }
}

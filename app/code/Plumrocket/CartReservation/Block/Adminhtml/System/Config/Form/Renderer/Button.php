<?php
/**
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\CartReservation\Block\Adminhtml\System\Config\Form\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * @since 2.4.0
 */
class Button extends AbstractRenderer
{

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function render(DataObject $row): string
    {
        return $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->addData(
                [
                    'id' => 'cartreservation_remove_button',
                    'label' => __('Remove'),
                    'type' => 'button',
                    'class' => 'delete dateperiod-remove ' . $this->getColumn()->getId()
                ]
            )
            ->toHtml();
    }
}

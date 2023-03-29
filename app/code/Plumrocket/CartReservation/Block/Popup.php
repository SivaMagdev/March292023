<?php
/**
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\CartReservation\Block;

use Magento\Framework\View\Element\Template\Context;
use Plumrocket\CartReservation\Helper\Config;

class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Plumrocket\CartReservation\Helper\Config
     */
    private $configHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Plumrocket\CartReservation\Helper\Config        $configHelper
     * @param array                                            $data
     */
    public function __construct(
        Context $context,
        Config $configHelper,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve custom template defined in admin config
     *
     * @return string
     */
    public function getContent()
    {
        $content = $this->configHelper->getAlertTemplate();
        // {{var product_list}} - variable that customer can use in admin config for output product list in popup
        return str_replace('{{var product_list}}', ' <!-- ko template: getTemplate() --><!-- /ko -->', $content);
    }
}

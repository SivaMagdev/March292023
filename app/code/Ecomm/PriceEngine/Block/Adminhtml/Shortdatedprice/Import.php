<?php

namespace Ecomm\PriceEngine\Block\Adminhtml\Shortdatedprice;

/**
 * Import block class
 */
class Import extends \Magento\Backend\Block\Template
{
    /**
     * Constructor
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
}

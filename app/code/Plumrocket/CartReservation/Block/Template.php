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

namespace Plumrocket\CartReservation\Block;

class Template extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Plumrocket\CartReservation\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Config
     */
    protected $configHelper;

    /**
     * Set if need to render block
     *
     * @var boolean
     */
    protected $renderBlock = true;

    /**
     * Set if need to display block
     *
     * @var boolean
     */
    protected $displayBlock = true;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Plumrocket\CartReservation\Helper\Data          $dataHelper
     * @param \Plumrocket\CartReservation\Helper\Config        $configHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Plumrocket\CartReservation\Helper\Data $dataHelper,
        \Plumrocket\CartReservation\Helper\Config $configHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get data helper
     *
     * @return \Plumrocket\CartReservation\Helper\Data
     */
    public function getDataHelper()
    {
        return $this->dataHelper;
    }

    /**
     * Get config helper
     *
     * @return \Plumrocket\CartReservation\Helper\Config
     */
    public function getConfigHelper()
    {
        return $this->configHelper;
    }

    /**
     * Set if need to render this block
     *
     * @param  boolean $flag
     * @return $this
     */
    public function renderBlock($flag = true)
    {
        $this->renderBlock = (bool)$flag;

        return $this;
    }

    /**
     * Set if need to display this block
     *
     * @param  boolean $flag
     * @return $this
     */
    public function displayBlock($flag = true)
    {
        $this->displayBlock = (bool)$flag;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (! $this->dataHelper->moduleEnabled() || ! $this->renderBlock) {
            return '';
        }

        $html = parent::_toHtml();
        if (! $this->displayBlock) {
            $html = '<div style="display: none;">' . $html . '</div>';
        }

        return $html;
    }
}

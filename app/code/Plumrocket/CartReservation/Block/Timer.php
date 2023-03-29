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
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\CartReservation\Block;

class Timer extends \Plumrocket\CartReservation\Block\Template
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Timer template
     *
     * @var string
     */
    protected $_template = 'Plumrocket_CartReservation::timer.phtml';

    /**
     * Text is show before countdown init
     *
     * @var string
     */
    protected $text = '...';

    /**
     * Css classes of timer
     *
     * @var string[]
     */
    protected $classes = [];

    /**
     * Css selectors to showing with timer
     *
     * @var string[]
     */
    protected $showSelectors = [];

    /**
     * Css selectors to hiding with timer
     *
     * @var string[]
     */
    protected $hideSelectors = [];

    /**
     * Additional data attributes
     *
     * @var string[]
     */
    protected $dataAttr = [];

    /**
     * List of product ids
     *
     * @var int[]
     */
    protected $productIdsToRequest = [];

    /**
     * @var array
     */
    protected $useBlockData = [];
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Plumrocket\CartReservation\Helper\Data          $dataHelper
     * @param \Plumrocket\CartReservation\Helper\Config        $configHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime      $dateTime
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Plumrocket\CartReservation\Helper\Data $dataHelper,
        \Plumrocket\CartReservation\Helper\Config $configHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($context, $dataHelper, $configHelper, $data);
    }

    /**
     * Get countdown format
     *
     * @return string
     */
    public function getCountdownFormat()
    {
        return $this->getDataAttr('format');
    }

    /**
     * Set countdown format
     *
     * @return $this
     */
    public function setCountdownFormat($countdownFormat)
    {
        return $this->setDataAttr('format', $countdownFormat);
    }

    /**
     * Get countdown layout
     *
     * @return string
     */
    public function getCountdownLayout()
    {
        return $this->getDataAttr('layout');
    }

    /**
     * Set countdown layout
     *
     * @return $this
     */
    public function setCountdownLayout($countdownLayout)
    {
        return $this->setDataAttr('layout', $countdownLayout);
    }

    /**
     * Get countdown labels few
     *
     * @return string
     */
    public function getCountdownLabelsFew()
    {
        return $this->getDataAttr('labels-few');
    }

    /**
     * Set countdown labels few
     *
     * @return $this
     */
    public function setCountdownLabelsFew($countdownLabelsFew)
    {
        return $this->setDataAttr('labels-few', $countdownLabelsFew);
    }

    /**
     * Get countdown labels one
     *
     * @return string
     */
    public function getCountdownLabelsOne()
    {
        return $this->getDataAttr('labels-one');
    }

    /**
     * Set countdown labels one
     *
     * @return $this
     */
    public function setCountdownLabelsOne($countdownLabelsOne)
    {
        return $this->setDataAttr('labels-one', $countdownLabelsOne);
    }

    /**
     * Get countdown time
     *
     * @return string
     */
    public function getCountdownTime()
    {
        return $this->getDataAttr('time');
    }

    /**
     * Set countdown time
     *
     * @return $this
     */
    public function setCountdownTime($countdownTime)
    {
        return $this->setDataAttr('time', $countdownTime);
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set text
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Check if timer is expired
     *
     * @return boolean
     */
    public function isExpired()
    {
        $now = $this->dateTime->gmtTimestamp();
        $expireAt = $this->getCountdownTime();

        return $expireAt > 0 && $now >= $expireAt;
    }

    /**
     * Get list of css classes
     *
     * @param  string|bool $glue
     * @return string|array
     */
    public function getClasses($glue = ' ')
    {
        $classes = $this->classes;
        if (false !== $glue) {
            $classes = join($glue, $classes);
        }

        return $classes;
    }

    /**
     * Add css class
     *
     * @param string|array $name
     * @return $this
     */
    public function addClass($name)
    {
        if (! is_array($name)) {
            $name = [$name];
        }

        foreach ($name as $value) {
            $this->classes[$value] = $value;
        }

        return $this;
    }

    /**
     * Remove css class
     *
     * @param  string $name
     * @return $this
     */
    public function removeClass($name)
    {
        unset($this->classes[$name]);
        return $this;
    }

    /**
     * Get css selectors that need to show with timer
     *
     * @param  string|bool $glue
     * @return string|array
     */
    public function getShowSelectors($glue = ', ')
    {
        $selectors = array_unique($this->showSelectors);
        if (false !== $glue) {
            $selectors = join($glue, $selectors);
        }

        return $selectors;
    }

    /**
     * Add selector to showing
     *
     * @param string $selector
     * @param $this
     */
    public function addShowSelector($selector)
    {
        if (is_array($selector)) {
            $this->showSelectors = array_merge($this->showSelectors, $selector);
        } else {
            $this->showSelectors[] = $selector;
        }

        return $this;
    }

    /**
     * Get css selectors that need to hide with timer
     *
     * @param  string|bool $glue
     * @return string|array
     */
    public function getHideSelectors($glue = ', ')
    {
        $selectors = array_unique($this->hideSelectors);
        if (false !== $glue) {
            $selectors = join($glue, $selectors);
        }

        return $selectors;
    }

    /**
     * Add selector to hiding
     *
     * @param $selector
     * @return $this
     */
    public function addHideSelector($selector)
    {
        if (is_array($selector)) {
            $this->hideSelectors = array_merge($this->hideSelectors, $selector);
        } else {
            $this->hideSelectors[] = $selector;
        }

        return $this;
    }

    /**
     * Set if need to show status with this timer
     *
     * @param bool $flag
     * @return $this
     */
    public function setShowStatus($flag = true)
    {
        return $this->setDataAttr('show-status', $flag? 1 : 0);
    }

    /**
     * Get all data attributes
     *
     * @param string $name
     * @return array|string
     */
    public function getDataAttr($name = null)
    {
        if (null !== $name) {
            return isset($this->dataAttr[$name]) ? $this->dataAttr[$name] : null;
        }

        return $this->dataAttr;
    }

    /**
     * Set data attribute
     *
     * @param string|array $key
     * @param string $value
     * @return $this
     */
    public function setDataAttr($key, $value = null)
    {
        if ($key === (array)$key) {
            $this->dataAttr = $key;
        } else {
            $this->dataAttr[$key] = $value;
        }

        return $this;
    }

    /**
     * Add data attributes
     *
     * @param array $data
     * @return $this
     */
    public function addDataAttr(array $data)
    {
        foreach ($data as $key => $value) {
            $this->setDataAttr($key, $value);
        }

        return $this;
    }

    /**
     * Remove data attribute
     *
     * @param string $name
     * @return $this
     */
    public function removeDataAttr($name)
    {
        unset($this->dataAttr[$name]);
        return $this;
    }

    /**
     * Set product ids and calculate children
     *
     * @param int|array $ids
     * @param bool $addToRequest
     * @return $this
     */
    public function setProductIds($ids, $addToRequest = true)
    {
        if (! is_array($ids)) {
            $ids = [$ids];
        }

        $children = [];
        foreach ($ids as $i => $id) {
            if ($i > 0) {
                $class = 'prcr_product_child_' . $id;
                $children[] = $id;
            } else {
                $class = 'prcr_product_' . $id;
            }

            $this->addClass($class);

            if ($addToRequest) {
                $this->addProductIdToRequest($id);
            }
        }

        $this->addClass('prcr_product');
        $this->addDataAttr([
            'product' => $ids[0],
            'children' => join(',', $children),
            'children-count' => count($children)
        ]);

        if (! $this->getCountdownLayout()) {
            $this->setCountdownLayout($this->getConfigHelper()->getTimerFormatOnProduct());
        }

        return $this;
    }

    /**
     * Add product id to request
     *
     * @param int $id
     */
    public function addProductIdToRequest($id)
    {
        $this->productIdsToRequest[] = $id;
    }

    /**
     * Get list of product ids
     *
     * @param  string|bool $glue
     * @return string|array
     */
    public function getProductIdsToRequest($glue = false)
    {
        $ids = array_unique($this->productIdsToRequest);
        if (false !== $glue) {
            $ids = join($glue, $ids);
        }

        return $ids;
    }

    /**
     * Set item ids and calculate children
     *
     * @param int|array $ids
     * @param bool $addToRequest
     * @return $this
     */
    public function setItemIds($ids)
    {
        if (! is_array($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $i => $id) {
            $class = 'prcr_item_' . $id;
            $this->addClass($class);
        }

        $this->addClass('prcr_item');
        $this->setDataAttr('item', $ids[0]);

        if (! $this->getCountdownLayout()) {
            $this->setCountdownLayout($this->getConfigHelper()->getTimerFormatSeparate());
        }

        return $this;
    }

    /**
     * Return block html without breaklines
     *
     * @return string
     */
    public function toHtmlOneLine()
    {
        $html = $this->toHtml();

        // Remove all line breaks, because they are replaced to "br" tag in product options.
        $html = str_replace(["\r", "\n"], '', $html);

        return $html;
    }

    /**
     * Specify where to find the cart item for timer
     *
     * @param string $blockName
     * @param string $dataKey
     * @return $this
     */
    public function bindBlockData($blockName, $dataKey)
    {
        if ($blockName && $dataKey) {
            $this->useBlockData = [
                'blockName' => $blockName,
                'dataKey' => $dataKey,
            ];
        } else {
            $this->useBlockData = [];
        }
        
        return $this;
    }

    /**
     * Get cart item from block and set for current timer
     *
     * @return $this
     */
    public function useBlockData()
    {
        if ($this->useBlockData) {
            if ($block = $this->getLayout()->getBlock($this->useBlockData['blockName'])) {
                if ($item = $block->getData($this->useBlockData['dataKey'])) {
                    $this->setItemIds($item->getId());
                }
            }
        }
        
        return $this;
    }
}

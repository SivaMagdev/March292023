<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Ecomm\LayeredNavigation\Plugin\Model\Layer\Filter;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Pager;
use Mageplaza\LayeredNavigation\Helper\Data as LayerHelper;

/**
 * Class Item
 * @package Mageplaza\LayeredNavigation\Model\Plugin\Layer\Filter
 */ 
class Item extends \Mageplaza\LayeredNavigation\Plugin\Model\Layer\Filter\Item
{
    /** @var UrlInterface */
    protected $_url;

    /** @var Pager */
    protected $_htmlPagerBlock;

    /** @var RequestInterface */
    protected $_request;

    /** @var LayerHelper */
    protected $_moduleHelper;

    /**
     * Item constructor.
     *
     * @param UrlInterface $url
     * @param Pager $htmlPagerBlock
     * @param RequestInterface $request
     * @param LayerHelper $moduleHelper
     */
    public function __construct(
        UrlInterface $url,
        Pager $htmlPagerBlock,
        RequestInterface $request,
        LayerHelper $moduleHelper
    ) {
        $this->_url = $url;
        $this->_htmlPagerBlock = $htmlPagerBlock;
        $this->_request = $request;
        $this->_moduleHelper = $moduleHelper;
    }

    

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\Item $item
     * @param $proceed
     *
     * @return string
     * @throws LocalizedException
     */
    public function aroundGetRemoveUrl(\Magento\Catalog\Model\Layer\Filter\Item $item, $proceed)
    {
        if (!$this->_moduleHelper->isEnabled()) {
            return $proceed();
        }

        $value = [];
        $filter = $item->getFilter();
        $filterModel = $this->_moduleHelper->getFilterModel();
        if ($filterModel->isMultiple($filter)) {
            $value = $filterModel->getFilterValue($filter);
            if (in_array((string)$item->getValue(), $value, true)) {
                $value = array_diff($value, [$item->getValue()]);
            }
        }

        $params['_query'] = [
            $filter->getRequestVar() => count($value) ? implode(',', $value) : $filter->getResetValue()
        ];
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_escape'] = true;

        $url= $this->_url->getUrl('*/*/*', $params);
        $base_url = strtok($url, '?');              
        $parsed_url = parse_url($url);   
        if(isset($parsed_url['query']))   
        {
            $query = $parsed_url['query'];              
            parse_str( $query, $parameters );           
            unset( $parameters['p'] );               
            $new_query = http_build_query($parameters); 
            $url= $base_url.((!empty($new_query))?'?'.$new_query:"");  
        }        
        
    
        
        return $url;
    }
}

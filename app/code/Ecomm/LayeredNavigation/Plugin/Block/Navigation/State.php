<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ecomm\LayeredNavigation\Plugin\Block\Navigation;

use Magento\Framework\View\Element\Template;

/**
 * Layered navigation state
 *
 * @api
 * @since 100.0.2
 */
class State extends \Magento\LayeredNavigation\Block\Navigation\State
{
    
    
    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function getClearUrl()
    {
        
        $filterState = [];
        foreach ($this->getActiveFilters() as $item) {
            $filterState[$item->getFilter()->getRequestVar()] = $item->getFilter()->getCleanValue();    
            
           
        }
       
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $filterState;
        $params['_escape'] = true;

        $url=$this->_urlBuilder->getUrl('*/*/*', $params);
        
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
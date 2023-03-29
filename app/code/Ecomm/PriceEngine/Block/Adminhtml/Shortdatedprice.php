<?php

namespace Ecomm\PriceEngine\Block\Adminhtml;

class Shortdatedprice extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Modify header & button labels
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_shortdatedprice';
        $this->_blockGroup = 'Ecomm_PriceEngine';
        $this->_headerText = __('Short Dated Price');
        $this->_addButtonLabel = __('Add New Short Dated Price');
        parent::_construct();
    }

    /**
     * Redefine header css class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'icon-head head-priceengine-shortdatedprice';
    }
}

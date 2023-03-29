<?php

namespace Ecomm\PriceEngine\Block\Adminhtml;

class Regularprice extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Modify header & button labels
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_regularprice';
        $this->_blockGroup = 'Ecomm_PriceEngine';
        $this->_headerText = __('Regular Price');
        $this->_addButtonLabel = __('Add New Regular Price');
        parent::_construct();
    }

    /**
     * Redefine header css class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'icon-head head-priceengine-regularprice';
    }
}

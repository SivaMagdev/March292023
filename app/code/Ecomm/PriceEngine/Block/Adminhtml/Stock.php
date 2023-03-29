<?php

namespace Ecomm\PriceEngine\Block\Adminhtml;

class Stock extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Modify header & button labels
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_stock';
        $this->_blockGroup = 'Ecomm_PriceEngine';
        $this->_headerText = __('Product Inventory');
        $this->_addButtonLabel = __('Add New Product Inventory');
        parent::_construct();
    }

    /**
     * Redefine header css class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'icon-head head-priceengine-stock';
    }
}

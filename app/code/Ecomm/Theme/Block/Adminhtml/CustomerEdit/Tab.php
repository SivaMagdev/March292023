<?php
namespace Ecomm\Theme\Block\Adminhtml\CustomerEdit;

class Tab extends \Magento\NegotiableQuote\Block\Adminhtml\CustomerEdit\Tab
{

    public function canShowTab()
    {
        // if (!$this->getRequest()->getParam('id')) {
        //     return false;
        // }

        // return $this->authorization->isAllowed('Magento_NegotiableQuote::view_quotes');
    }

}
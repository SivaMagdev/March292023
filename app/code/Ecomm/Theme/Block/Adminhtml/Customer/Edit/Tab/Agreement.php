<?php
namespace Ecomm\Theme\Block\Adminhtml\Customer\Edit\Tab;

class Agreement extends \Magento\Paypal\Block\Adminhtml\Customer\Edit\Tab\Agreement
{

    public function canShowTab()
    {
        // return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID) !== null;
    }

}
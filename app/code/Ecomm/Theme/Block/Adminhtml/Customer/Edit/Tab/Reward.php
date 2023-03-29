<?php
namespace Ecomm\Theme\Block\Adminhtml\Customer\Edit\Tab;

class Reward extends \Magento\Reward\Block\Adminhtml\Customer\Edit\Tab\Reward
{

        public function canShowTab()
    {
        // $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        // return $customerId && $this->_rewardData->isEnabled() && $this->_authorization->isAllowed(
        //     \Magento\Reward\Helper\Data::XML_PATH_PERMISSION_BALANCE
        // );
    }

}
<?php
namespace Ecomm\Theme\Block\Adminhtml;

class ReviewTab extends \Magento\Review\Block\Adminhtml\ReviewTab
{

    public function canShowTab()
    {
        // return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

}
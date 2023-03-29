<?php

namespace Ecomm\ExclusivePrice\Controller\Adminhtml\ContractPrice;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Edit extends \Ecomm\ExclusivePrice\Controller\Adminhtml\ContractPrice {
    /**
     * Edit customer group action. Forward to new action.
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $dataId = $this->getRequest()->getParam('entity_id');
        
        $resultPage = $this->_resultPageFactory->create();

        if ($dataId === null) {
            $resultPage->getConfig()->getTitle()->prepend(__('New Contract List'));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(
                $this->dataRepository->getById($dataId)->getContractId()
            );
        }
        return $resultPage;
    }
}

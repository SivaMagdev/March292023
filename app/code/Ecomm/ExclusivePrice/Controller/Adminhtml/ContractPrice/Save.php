<?php

namespace Ecomm\ExclusivePrice\Controller\Adminhtml\ContractPrice;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Controller class Save. Performs save action of customers group
 */
class Save extends \Ecomm\ExclusivePrice\Controller\Adminhtml\ContractPrice implements HttpPostActionInterface
{

    /**
     * Create or save customer group.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {   
        $group = '';
        $isPost = $this->getRequest()->isPost();
        if (!empty($this->getRequest()->getParam('group_id'))) {
            $group = $this->groupRepository->getById($this->getRequest()->getParam('group_id'));
        }
        if ($isPost) {
            $contract_price_id = 0;

            $contractpriceModel = $this->_contractpriceFactory->create();

            if ($this->getRequest()->getParam('entity_id') !== null) {
                $contract_price_id = $this->getRequest()->getParam('entity_id');
            }

            $contractpriceModels = $contractpriceModel->getCollection()
                ->addFieldToFilter('contract_id', $this->getRequest()->getParam('contract_id'));

            if ($contract_price_id > 0) {
                $contractpriceModel->load($contract_price_id);
            } else {
                $contractpriceModel->setCreatedAt(date('Y-m-d'));
            }

            $contractpriceModel->setContractId($this->getRequest()->getParam('contract_id'));
            $contractpriceModel->setContractType($this->getRequest()->getParam('contract_type'));
            $contractpriceModel->setGpoName($group->getCode());
            $contractpriceModel->setIsDsh($this->getRequest()->getParam('is_dsh'));
            $contractpriceModel->setStatus($this->getRequest()->getParam('status'));
            $contractpriceModel->setCreatedBy($this->getRequest()->getParam('created_by'));
            $contractpriceModel->setGroupId($this->getRequest()->getParam('group_id'));
            $contractpriceModel->setName($this->getRequest()->getParam('name'));

            try {
                // Save city
                $contractpriceModel->save();

                // Display success message
                $this->messageManager->addSuccess(__('Price has been saved.'));

                // Check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $contractpriceModel->getId(), '_current' => true]);
                    return;
                }
                $this->_getSession()->setFormData(null);
                // Go to grid page
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

            $this->_getSession()->setFormData($formData);
            $this->_redirect('*/*/edit', ['id' => $contract_price_id]);
        }
    }
}

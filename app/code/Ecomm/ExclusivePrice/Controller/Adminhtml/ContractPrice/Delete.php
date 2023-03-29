<?php

namespace Ecomm\ExclusivePrice\Controller\Adminhtml\ContractPrice;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends \Ecomm\ExclusivePrice\Controller\Adminhtml\ContractPrice implements HttpPostActionInterface
{
    /**
     * Delete area.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $contractpriceModel = $this->_contractpriceFactory->create();
                $contractpriceModel->load($id);
                $contractpriceModel->setStatus('0');
                $contractpriceModel->setDeleted('1');
                $contractpriceModel->save();
                // $contractpriceModel->delete();

                $this->messageManager->addSuccess(__('You deleted the Price.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError(__('The area no longer exists.'));
                return $resultRedirect->setPath('ecomm_exclusiveprice/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('ecomm_exclusiveprice/contractprice/edit', ['id' => $id]);
            }
        }
        return $resultRedirect->setPath('ecomm_exclusiveprice/contractprice');
    }
}

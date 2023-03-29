<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\ExclusivePrice;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends \Ecomm\PriceEngine\Controller\Adminhtml\ExclusivePrice implements HttpPostActionInterface
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
                $exclusivepriceModel = $this->_exclusivepriceFactory->create();
                $exclusivepriceModel->load($id);
                $exclusivepriceModel->delete();

                $this->messageManager->addSuccess(__('You deleted the Price.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError(__('The area no longer exists.'));
                return $resultRedirect->setPath('ecomm_priceengine/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('ecomm_priceengine/exclusiveprice/edit', ['id' => $id]);
            }
        }
        return $resultRedirect->setPath('ecomm_priceengine/exclusiveprice');
    }
}

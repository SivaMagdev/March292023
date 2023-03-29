<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Shortdatedprice;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends \Ecomm\PriceEngine\Controller\Adminhtml\Shortdatedprice implements HttpPostActionInterface
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
                $shortdatedpriceModel = $this->_shortdatedpriceFactory->create();
                $shortdatedpriceModel->load($id);
                $shortdatedpriceModel->delete();

                $this->messageManager->addSuccess(__('You deleted the Price.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError(__('The area no longer exists.'));
                return $resultRedirect->setPath('ecomm_priceengine/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('ecomm_priceengine/shortdatedprice/edit', ['id' => $id]);
            }
        }
        return $resultRedirect->setPath('ecomm_priceengine/shortdatedprice');
    }
}

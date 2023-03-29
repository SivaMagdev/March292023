<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Stock;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Controller class Save. Performs save action of customers group
 */
class Save extends \Ecomm\PriceEngine\Controller\Adminhtml\Stock implements HttpPostActionInterface
{

    /**
     * Create or save customer group.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $isPost = $this->getRequest()->isPost();

        if ($isPost) {
            $stock_id = 0;

            $stockModel = $this->_stockFactory->create();

            if ($this->getRequest()->getParam('stock_id') !== null) {
                $stock_id = $this->getRequest()->getParam('stock_id');
            }

            $stockModels = $stockModel->getCollection()
                ->addFieldToFilter('code', $this->getRequest()->getParam('code'));

            if ($stock_id > 0) {
                $stockModels = $stockModels->addFieldToFilter('stock_id', ['neq'=>$stock_id]);
            }

            //echo 'areaCount: '.$areaModels->count().'<br />';


            if ($stock_id > 0) {
                $stockModel->load($stock_id);
            } else {
                $stockModel->setCreatedAt(date('Y-m-d'));
            }
            $stockModel->setProductSku($this->getRequest()->getParam('product_sku'));
            $stockModel->setStock($this->getRequest()->getParam('stock'));
            $stockModel->setCreatedBy($this->authSession->getUser()->getUsername());
            $stockModel->setStartDate($this->getRequest()->getParam('start_date'));
            $stockModel->setEndDate($this->getRequest()->getParam('end_date'));

            try {
                // Save city
                $stockModel->save();

                // Display success message
                $this->messageManager->addSuccess(__('Price has been saved.'));

                // Check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $stockModel->getId(), '_current' => true]);
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
            $this->_redirect('*/*/edit', ['id' => $stock_id]);
        }
    }
}

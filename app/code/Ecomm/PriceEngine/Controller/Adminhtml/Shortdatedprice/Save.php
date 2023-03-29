<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Shortdatedprice;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Controller class Save. Performs save action of customers group
 */
class Save extends \Ecomm\PriceEngine\Controller\Adminhtml\Shortdatedprice implements HttpPostActionInterface
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
            $shortdated_price_id = 0;

            $shortdatedpriceModel = $this->_shortdatedpriceFactory->create();

            if ($this->getRequest()->getParam('shortdated_price_id') !== null) {
                $shortdated_price_id = $this->getRequest()->getParam('shortdated_price_id');
            }

            $shortdatedpriceModels = $shortdatedpriceModel->getCollection()
                ->addFieldToFilter('code', $this->getRequest()->getParam('code'));

            if ($shortdated_price_id > 0) {
                $shortdatedpriceModels = $shortdatedpriceModels->addFieldToFilter('shortdated_price_id', ['neq'=>$shortdated_price_id]);
            }

            //echo 'areaCount: '.$areaModels->count().'<br />';


            if ($shortdated_price_id > 0) {
                $shortdatedpriceModel->load($shortdated_price_id);
            } else {
                $shortdatedpriceModel->setCreatedAt(date('Y-m-d'));
            }

            $shortdatedpriceModel->setName($this->getRequest()->getParam('name'));
            $shortdatedpriceModel->setProductSku($this->getRequest()->getParam('product_sku'));
            $shortdatedpriceModel->setNdc($this->getRequest()->getParam('ndc'));
            $shortdatedpriceModel->setStrengthCount($this->getRequest()->getParam('strength_count'));
            $shortdatedpriceModel->setPackSize($this->getRequest()->getParam('pack_size'));
            $shortdatedpriceModel->setShortdatedPrice($this->getRequest()->getParam('shortdated_price'));
            $shortdatedpriceModel->setInventory($this->getRequest()->getParam('inventory'));
            $shortdatedpriceModel->setBatch($this->getRequest()->getParam('batch'));
            $shortdatedpriceModel->setExpiryDate($this->getRequest()->getParam('expiry_date'));
            $shortdatedpriceModel->setStartDate($this->getRequest()->getParam('start_date'));
            $shortdatedpriceModel->setEndDate($this->getRequest()->getParam('end_date'));

            try {
                // Save city
                $shortdatedpriceModel->save();

                // Display success message
                $this->messageManager->addSuccess(__('Price has been saved.'));

                // Check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $shortdatedpriceModel->getId(), '_current' => true]);
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
            $this->_redirect('*/*/edit', ['id' => $shortdated_price_id]);
        }
    }
}

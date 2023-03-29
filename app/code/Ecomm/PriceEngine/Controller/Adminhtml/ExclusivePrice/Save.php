<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\ExclusivePrice;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Controller class Save. Performs save action of customers group
 */
class Save extends \Ecomm\PriceEngine\Controller\Adminhtml\ExclusivePrice implements HttpPostActionInterface
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
            $exclusive_price_id = 0;

            $exclusivepriceModel = $this->_exclusivepriceFactory->create();

            if ($this->getRequest()->getParam('exclusive_price_id') !== null) {
                $exclusive_price_id = $this->getRequest()->getParam('exclusive_price_id');
            }

            $exclusivepriceModels = $exclusivepriceModel->getCollection()
                ->addFieldToFilter('code', $this->getRequest()->getParam('code'));

            if ($exclusive_price_id > 0) {
                $exclusivepriceModels = $exclusivepriceModels->addFieldToFilter('exclusive_price_id', ['neq'=>$exclusive_price_id]);
            }

            //echo 'areaCount: '.$areaModels->count().'<br />';


            if ($exclusive_price_id > 0) {
                $exclusivepriceModel->load($exclusive_price_id);
            } else {
                $exclusivepriceModel->setCreatedAt(date('Y-m-d'));
            }

            $exclusivepriceModel->setName($this->getRequest()->getParam('name'));
            $exclusivepriceModel->setProductSku($this->getRequest()->getParam('product_sku'));
            $exclusivepriceModel->setNdc($this->getRequest()->getParam('ndc'));
            $exclusivepriceModel->setStrengthCount($this->getRequest()->getParam('strength_count'));
            $exclusivepriceModel->setPackSize($this->getRequest()->getParam('pack_size'));
            $exclusivepriceModel->setCustomerId($this->getRequest()->getParam('customer_id'));
            $exclusivepriceModel->setPrice($this->getRequest()->getParam('price'));
            $exclusivepriceModel->setStartDate($this->getRequest()->getParam('start_date'));
            $exclusivepriceModel->setEndDate($this->getRequest()->getParam('end_date'));
            $exclusivepriceModel->setContractRef($this->getRequest()->getParam('contract_ref'));


            /*$formData = array(
                'area_id'=>$area_id,
                'name'=>$this->getRequest()->getParam('name'),
                'status'=>$this->getRequest()->getParam('status'),
            );

            echo '<pre>'.print_r($formData, true).'</pre>';
            exit();
            $areaModel->setData($formData);*/

            try {
                // Save city
                $exclusivepriceModel->save();

                // Display success message
                $this->messageManager->addSuccess(__('Price has been saved.'));

                // Check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $exclusivepriceModel->getId(), '_current' => true]);
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
            $this->_redirect('*/*/edit', ['id' => $exclusive_price_id]);
        }
    }
}

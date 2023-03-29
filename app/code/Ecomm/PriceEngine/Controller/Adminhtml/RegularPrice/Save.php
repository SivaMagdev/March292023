<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\RegularPrice;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Controller class Save. Performs save action of customers group
 */
class Save extends \Ecomm\PriceEngine\Controller\Adminhtml\RegularPrice implements HttpPostActionInterface
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
            $gpo_price_id = 0;

            $regularpriceModel = $this->_regularpriceFactory->create();

            if ($this->getRequest()->getParam('gpo_price_id') !== null) {
                $gpo_price_id = $this->getRequest()->getParam('gpo_price_id');
            }

            $regularpriceModels = $regularpriceModel->getCollection()
                ->addFieldToFilter('code', $this->getRequest()->getParam('code'));

            if ($gpo_price_id > 0) {
                $regularpriceModels = $regularpriceModels->addFieldToFilter('gpo_price_id', ['neq'=>$gpo_price_id]);
            }

            //echo 'areaCount: '.$areaModels->count().'<br />';


            if ($gpo_price_id > 0) {
                $regularpriceModel->load($gpo_price_id);
            } else {
                $regularpriceModel->setCreatedAt(date('Y-m-d'));
            }

            $regularpriceModel->setName($this->getRequest()->getParam('name'));
            $regularpriceModel->setProductSku($this->getRequest()->getParam('product_sku'));
            $regularpriceModel->setNdc($this->getRequest()->getParam('ndc'));
            $regularpriceModel->setStrengthCount($this->getRequest()->getParam('strength_count'));
            $regularpriceModel->setPackSize($this->getRequest()->getParam('pack_size'));
            $regularpriceModel->setGpoName($this->getRequest()->getParam('gpo_name'));
            $regularpriceModel->setGpoPrice($this->getRequest()->getParam('gpo_price'));
            $regularpriceModel->setDishPrice($this->getRequest()->getParam('dish_price'));
            $regularpriceModel->setDirectPrice($this->getRequest()->getParam('direct_price'));
            $regularpriceModel->setStartDate($this->getRequest()->getParam('start_date'));
            $regularpriceModel->setEndDate($this->getRequest()->getParam('end_date'));
            $regularpriceModel->setGpoRef($this->getRequest()->getParam('gpo_ref'));


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
                $regularpriceModel->save();

                // Display success message
                $this->messageManager->addSuccess(__('Price has been saved.'));

                // Check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $regularpriceModel->getId(), '_current' => true]);
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
            $this->_redirect('*/*/edit', ['id' => $gpo_price_id]);
        }
    }
}

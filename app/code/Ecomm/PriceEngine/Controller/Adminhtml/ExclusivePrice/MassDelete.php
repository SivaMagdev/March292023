<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\ExclusivePrice;

use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Magento\Backend\App\Action
{

    public function execute()
    {
        $exclusivepriceIds = $this->getRequest()->getParam('selected');
        if (!is_array($exclusivepriceIds) || empty($exclusivepriceIds)) {
            $this->messageManager->addErrorMessage(__('Please select Price(s).'));
        } else {
            try {
                foreach ($exclusivepriceIds as $exclusive_price_id) {
                    $exclusiveprice = $this->_objectManager->create('Ecomm\PriceEngine\Model\ExclusivePrice')
                        ->load($exclusive_price_id);
                    $exclusiveprice->delete();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($exclusivepriceIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index');
    }
}

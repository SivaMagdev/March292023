<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Shortdatedprice;

use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Magento\Backend\App\Action
{

    public function execute()
    {
        $shortdatedpriceIds = $this->getRequest()->getParam('selected');
        if (!is_array($shortdatedpriceIds) || empty($shortdatedpriceIds)) {
            $this->messageManager->addErrorMessage(__('Please select Price(s).'));
        } else {
            try {
                foreach ($shortdatedpriceIds as $shortdated_price_id) {
                    $shortdatedpriceModel = $this->_objectManager->create('Ecomm\PriceEngine\Model\Shortdatedprice')
                        ->load($shortdated_price_id);
                    $shortdatedpriceModel->delete();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($shortdatedpriceIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index');
    }
}

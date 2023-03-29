<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\RegularPrice;

use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Magento\Backend\App\Action
{

    public function execute()
    {
        $regularpriceIds = $this->getRequest()->getParam('selected');
        if (!is_array($regularpriceIds) || empty($regularpriceIds)) {
            $this->messageManager->addErrorMessage(__('Please select Price(s).'));
        } else {
            try {
                foreach ($regularpriceIds as $gpo_price_id) {
                    $regularprice = $this->_objectManager->create('Ecomm\PriceEngine\Model\RegularPrice')
                        ->load($gpo_price_id);
                    $regularprice->delete();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($regularpriceIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index');
    }
}

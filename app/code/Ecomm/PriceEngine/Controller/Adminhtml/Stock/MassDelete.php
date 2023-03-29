<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Stock;

use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Magento\Backend\App\Action
{

    public function execute()
    {
        //echo '<pre>'.print_r($this->getRequest(), true).'</pre>';
        $stockIds = $this->getRequest()->getParam('selected');
        //echo '<pre>'.print_r($stockIds, true).'</pre>';
        //exit();
        if (!is_array($stockIds) || empty($stockIds)) {
            $this->messageManager->addErrorMessage(__('Please select Stock(s).'));
        } else {
            try {
                foreach ($stockIds as $stock_id) {
                    $stock = $this->_objectManager->create('Ecomm\PriceEngine\Model\Stock')
                        ->load($stock_id);
                    $stock->delete();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($stockIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index');
    }
}

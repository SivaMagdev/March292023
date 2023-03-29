<?php

namespace Ecomm\ExclusivePrice\Controller\Adminhtml\ContractPrice;

class MassDelete extends \Magento\Backend\App\Action
{

    public function execute()
    {
        $contractpriceIds = $this->getRequest()->getParam('selected');
        // var_dump($contractpriceIds);
        // die;
        if (!is_array($contractpriceIds) || empty($contractpriceIds)) {
            $this->messageManager->addErrorMessage(__('Please select Price(s).'));
        } else {
            try {
                foreach ($contractpriceIds as $contract_price_id) {
                    $contractprice = $this->_objectManager->create('Ecomm\ExclusivePrice\Model\ContractPrice')
                        ->load($contract_price_id);
                    $contractprice->setStatus('0');
                    $contractprice->setDeleted('1');
                    $contractprice->save();
                    // $exclusiveprice->delete();
                }
                // $contractprice->getSelect()->__toString();
                // echo $collection->getSelect();
                // die;
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($contractpriceIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index');
    }
}

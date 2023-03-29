<?php

namespace Ecomm\ExclusivePrice\Controller\Adminhtml\ContractPrice;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Ecomm\ExclusivePrice\Controller\RegistryConstants;

class NewAction extends \Ecomm\ExclusivePrice\Controller\Adminhtml\ContractPrice implements HttpGetActionInterface
{

    // const CURRENT_CONTRACTPRICE_ID = 'current_contractprice_id';

    /**
     * Initialize current group and set it in the registry.
     *
     * @return int
     */
    protected function _initContractPrice()
    {
        $contract_price_id = $this->getRequest()->getParam('id');

        return $contract_price_id;
    }

    /**
     * Edit or create price.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $contract_price_id = $this->_initContractPrice();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();

        $model = $this->_contractpriceFactory->create();

        $resultPage->setActiveMenu('Ecomm_ExclusivePrice::contractprice');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Contract Price'));
        $resultPage->addBreadcrumb(__('Contract Price'), __('Contract Price'));
        $resultPage->addBreadcrumb(__(' Contract Price List'), __(' Contract Price List'), $this->getUrl('ecomm_contractprice/contractprice'));

        if ($contract_price_id === null) {
            $resultPage->addBreadcrumb(__('New Contract Price'), __('New Contract Price'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Contract Price'));
        } else {
            $model->load($contract_price_id);
            $this->_coreRegistry->register(RegistryConstants::CURRENT_CONTRACTPRICE_ID, $contract_price_id);
            $this->_coreRegistry->register('exclusiveprice_contractprice', $model);
            $resultPage->addBreadcrumb(__('Edit Contract Price'), __('Edit Contract Price'));
            $resultPage->getConfig()->getTitle()->prepend(
                //$this->groupRepository->getById($exclusive_price_id)->getName()
                $model->getName()
            );
        }

        $resultPage->getLayout()->addBlock(\Ecomm\ExclusivePrice\Block\Adminhtml\Contractprice\Edit::class, 'contract_price', 'content')
            ->setEditMode((bool)$contract_price_id);

        return $resultPage;
    }
}

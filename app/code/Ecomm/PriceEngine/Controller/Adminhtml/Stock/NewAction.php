<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Stock;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Ecomm\PriceEngine\Controller\RegistryConstants;

class NewAction extends \Ecomm\PriceEngine\Controller\Adminhtml\Stock implements HttpGetActionInterface
{
    /**
     * Initialize current group and set it in the registry.
     *
     * @return int
     */
    protected function _initStock()
    {
        $stock_id = $this->getRequest()->getParam('id');

        return $stock_id;
    }

    /**
     * Edit or create price.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $stock_id = $this->_initStock();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();

        $model = $this->_stockFactory->create();

        $resultPage->setActiveMenu('Ecomm_PriceEngine::stock');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Stock List'));
        $resultPage->addBreadcrumb(__('Price Engine'), __('Price Engine'));
        $resultPage->addBreadcrumb(__(' Stock List'), __(' Stock List'), $this->getUrl('ecomm_priceengine/stock'));

        if ($stock_id === null) {
            $resultPage->addBreadcrumb(__('New Stock'), __('New Stock'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Stock'));
        } else {
            $model->load($stock_id);
            $this->_coreRegistry->register(RegistryConstants::CURRENT_STOCK_ID, $stock_id);
            $this->_coreRegistry->register('priceengine_stock', $model);
            $resultPage->addBreadcrumb(__('Edit Stock'), __('Edit Stock'));
            $resultPage->getConfig()->getTitle()->prepend(
                //$this->groupRepository->getById($stock_id)->getName()
                $model->getName()
            );
        }

        $resultPage->getLayout()->addBlock(\Ecomm\PriceEngine\Block\Adminhtml\Stock\Edit::class, 'stock', 'content')
            ->setEditMode((bool)$stock_id);

        return $resultPage;
    }
}

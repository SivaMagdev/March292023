<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Shortdatedprice;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Ecomm\PriceEngine\Controller\RegistryConstants;

class NewAction extends \Ecomm\PriceEngine\Controller\Adminhtml\Shortdatedprice implements HttpGetActionInterface
{
    /**
     * Initialize current group and set it in the registry.
     *
     * @return int
     */
    protected function _initShortdatePrice()
    {
        $shortdated_price_id = $this->getRequest()->getParam('id');

        return $shortdated_price_id;
    }

    /**
     * Edit or create price.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $shortdated_price_id = $this->_initShortdatePrice();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();

        $model = $this->_shortdatedpriceFactory->create();

        $resultPage->setActiveMenu('Ecomm_PriceEngine::shortdatedprice');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Short Dated Price'));
        $resultPage->addBreadcrumb(__('Price Engine'), __('Price Engine'));
        $resultPage->addBreadcrumb(__(' Short Dated Price List'), __(' Short Dated Price List'), $this->getUrl('ecomm_priceengine/shortdatedprice'));

        if ($shortdated_price_id === null) {
            $resultPage->addBreadcrumb(__('New Short Dated Price'), __('New Short Dated Price'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Short Dated Price'));
        } else {
            $model->load($shortdated_price_id);
            $this->_coreRegistry->register(RegistryConstants::CURRENT_SHORTDATEDPRICE_ID, $shortdated_price_id);
            $this->_coreRegistry->register('priceengine_shortdatedprice', $model);
            $resultPage->addBreadcrumb(__('Edit Short Dated Price'), __('Edit Short Dated Price'));
            $resultPage->getConfig()->getTitle()->prepend(
                //$this->groupRepository->getById($shortdated_price_id)->getName()
                $model->getName()
            );
        }

        $resultPage->getLayout()->addBlock(\Ecomm\PriceEngine\Block\Adminhtml\Shortdatedprice\Edit::class, 'shortdatedprice', 'content')
            ->setEditMode((bool)$shortdated_price_id);

        return $resultPage;
    }
}

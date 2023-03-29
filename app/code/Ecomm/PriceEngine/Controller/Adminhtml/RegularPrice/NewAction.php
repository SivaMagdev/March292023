<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\RegularPrice;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Ecomm\PriceEngine\Controller\RegistryConstants;

class NewAction extends \Ecomm\PriceEngine\Controller\Adminhtml\RegularPrice implements HttpGetActionInterface
{
    /**
     * Initialize current group and set it in the registry.
     *
     * @return int
     */
    protected function _initRegularPrice()
    {
        $gpo_price_id = $this->getRequest()->getParam('id');

        return $gpo_price_id;
    }

    /**
     * Edit or create price.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $gpo_price_id = $this->_initRegularPrice();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();

        $model = $this->_regularpriceFactory->create();

        $resultPage->setActiveMenu('Ecomm_PriceEngine::regularprice');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Regular Price'));
        $resultPage->addBreadcrumb(__('Price Engine'), __('Price Engine'));
        $resultPage->addBreadcrumb(__(' Regular Price List'), __(' Regular Price List'), $this->getUrl('ecomm_priceengine/regularprice'));

        if ($gpo_price_id === null) {
            $resultPage->addBreadcrumb(__('New Regular Price'), __('New Regular Price'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Regular Price'));
        } else {
            $model->load($gpo_price_id);
            $this->_coreRegistry->register(RegistryConstants::CURRENT_REGULARPRICE_ID, $gpo_price_id);
            $this->_coreRegistry->register('priceengine_regularprice', $model);
            $resultPage->addBreadcrumb(__('Edit Regular Price'), __('Edit Regular Price'));
            $resultPage->getConfig()->getTitle()->prepend(
                //$this->groupRepository->getById($gpo_price_id)->getName()
                $model->getName()
            );
        }

        $resultPage->getLayout()->addBlock(\Ecomm\PriceEngine\Block\Adminhtml\Regularprice\Edit::class, 'regular_price', 'content')
            ->setEditMode((bool)$gpo_price_id);

        return $resultPage;
    }
}

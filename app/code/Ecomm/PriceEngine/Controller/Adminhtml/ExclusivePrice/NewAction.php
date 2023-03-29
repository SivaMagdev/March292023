<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\ExclusivePrice;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Ecomm\PriceEngine\Controller\RegistryConstants;

class NewAction extends \Ecomm\PriceEngine\Controller\Adminhtml\ExclusivePrice implements HttpGetActionInterface
{
    /**
     * Initialize current group and set it in the registry.
     *
     * @return int
     */
    protected function _initExclusivePrice()
    {
        $exclusive_price_id = $this->getRequest()->getParam('id');

        return $exclusive_price_id;
    }

    /**
     * Edit or create price.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $exclusive_price_id = $this->_initExclusivePrice();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();

        $model = $this->_exclusivepriceFactory->create();

        $resultPage->setActiveMenu('Ecomm_PriceEngine::exclusiveprice');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Exclusive Price'));
        $resultPage->addBreadcrumb(__('Price Engine'), __('Price Engine'));
        $resultPage->addBreadcrumb(__(' Exclusive Price List'), __(' Exclusive Price List'), $this->getUrl('ecomm_priceengine/exclusiveprice'));

        if ($exclusive_price_id === null) {
            $resultPage->addBreadcrumb(__('New Exclusive Price'), __('New Exclusive Price'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Exclusive Price'));
        } else {
            $model->load($exclusive_price_id);
            $this->_coreRegistry->register(RegistryConstants::CURRENT_EXCLUSIVEPRICE_ID, $exclusive_price_id);
            $this->_coreRegistry->register('priceengine_exclusiveprice', $model);
            $resultPage->addBreadcrumb(__('Edit Exclusive Price'), __('Edit Exclusive Price'));
            $resultPage->getConfig()->getTitle()->prepend(
                //$this->groupRepository->getById($exclusive_price_id)->getName()
                $model->getName()
            );
        }

        $resultPage->getLayout()->addBlock(\Ecomm\PriceEngine\Block\Adminhtml\Exclusiveprice\Edit::class, 'exclusive_price', 'content')
            ->setEditMode((bool)$exclusive_price_id);

        return $resultPage;
    }
}

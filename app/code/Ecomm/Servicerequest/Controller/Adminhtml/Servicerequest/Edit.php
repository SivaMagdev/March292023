<?php

namespace Ecomm\Servicerequest\Controller\Adminhtml\Servicerequest;

use Ecomm\Servicerequest\Controller\Adminhtml\Servicerequest;

class Edit extends Servicerequest
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $dataId = $this->getRequest()->getParam('id');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ecomm_Servicerequest::servicerequest')
            ->addBreadcrumb(__('Service request'), __('Service request'))
            ->addBreadcrumb(__('Manage Service request'), __('Manage Service request'));

        if ($dataId === null) {
            $resultPage->addBreadcrumb(__('New Service request'), __('New Service request'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Service request'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Service request'), __('Edit Service request'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->dataRepository->getById($dataId)->getName()
            );
        }
        return $resultPage;
    }
}

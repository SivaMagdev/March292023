<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 *
 * ==============================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * ==============================================================
 * This package designed for Magento COMMUNITY edition
 * PWC does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PWC does not provide extension support in case of
 * incorrect edition usage.
 * ==============================================================
 *
 * @category    PWC
 * @package     Ecomm_Supportdocument
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\Supportdocument\Controller\Adminhtml\Supportdocument;

use Ecomm\Supportdocument\Controller\Adminhtml\Supportdocument;

class Edit extends Supportdocument
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $dataId = $this->getRequest()->getParam('id');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ecomm_Supportdocument::manage_Supportdocument')
            ->addBreadcrumb(__('Supportdocument'), __('Supportdocument'))
            ->addBreadcrumb(__('Manage Supportdocument'), __('Manage Supportdocument'));

        if ($dataId === null) {
            $resultPage->addBreadcrumb(__('New Support Document'), __('New Support Document'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Support Document'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Support Document'), __('Edit Support Document'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->dataRepository->getById($dataId)->getName()
            );
        }
        return $resultPage;
    }
}

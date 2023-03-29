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
 * @package     Ecomm_News
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\News\Controller\Adminhtml\News;

use Ecomm\News\Controller\Adminhtml\News;

class Edit extends News
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $dataId = $this->getRequest()->getParam('id');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ecomm_News::manage_News')
            ->addBreadcrumb(__('News'), __('News'))
            ->addBreadcrumb(__('Manage News'), __('Manage News'));

        if ($dataId === null) {
            $resultPage->addBreadcrumb(__('New News'), __('New News'));
            $resultPage->getConfig()->getTitle()->prepend(__('New News'));
        } else {
            $resultPage->addBreadcrumb(__('Edit News'), __('Edit News'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->dataRepository->getById($dataId)->getTitle()
            );
        }
        return $resultPage;
    }
}

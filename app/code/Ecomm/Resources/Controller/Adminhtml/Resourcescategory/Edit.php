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
 * @package     Ecomm_Resources
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\Resources\Controller\Adminhtml\Resourcescategory;

use Ecomm\Resources\Controller\Adminhtml\Resourcescategory;

class Edit extends Resourcescategory
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $dataId = $this->getRequest()->getParam('id');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ecomm_Resources::manage_Resources')
            ->addBreadcrumb(__('Resources'), __('Resources'))
            ->addBreadcrumb(__('Manage Resources Category'), __('Manage Resources Category'));

        if ($dataId === null) {
            $resultPage->addBreadcrumb(__('New Resources Category'), __('New Resources Category'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Resources Category'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Resources Category'), __('Edit Resources Category'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->dataRepository->getById($dataId)->getName()
            );
        }
        return $resultPage;
    }
}

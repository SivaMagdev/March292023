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
 * @package     Ecomm_Voiceofcustomer
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\Voiceofcustomer\Controller\Adminhtml\Voiceofcustomer;

use Ecomm\Voiceofcustomer\Controller\Adminhtml\Voiceofcustomer;

class Edit extends Voiceofcustomer
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $dataId = $this->getRequest()->getParam('id');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ecomm_Voiceofcustomer::manage_Voiceofcustomer')
            ->addBreadcrumb(__('Voiceofcustomer'), __('Voiceofcustomer'))
            ->addBreadcrumb(__('Manage Voiceofcustomer'), __('Manage Voiceofcustomer'));

        if ($dataId === null) {
            $resultPage->addBreadcrumb(__('New Voice of customer'), __('New Voice of customer'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Voice of customer'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Voiceofcustomer'), __('Edit Voiceofcustomer'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->dataRepository->getById($dataId)->getName()
            );
        }
        return $resultPage;
    }
}

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

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{   
    const ADMIN_RESOURCE = 'Ecomm_Voiceofcustomer::voiceofcustomer';
        /**
         * @var \Magento\Framework\View\Result\PageFactory
         */
        protected $resultPageFactory;

        /**
         * @param \Magento\Framework\App\Action\Context $context
         * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
         */
        public function __construct(
            \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory
        )
        {
            parent::__construct($context);
            $this->resultPageFactory = $resultPageFactory;
        }
    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ecomm_Voiceofcustomer::manage_Voiceofcustomer');
        $resultPage->addBreadcrumb(__('Voice of customer Listing'), __('Voice of customer Listing'));
        $resultPage->getConfig()->getTitle()->prepend(__('Voice of customer Listing'));

        return $resultPage;
    }
}?>
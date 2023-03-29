<?php

/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_GcpIntegration
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\GcpIntegration\Controller\Adminhtml\Mendix;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Index controller class
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * Constructor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        DirectoryList $directoryList
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ecomm_GcpIntegration::gcs');
        $resultPage->getConfig()->getTitle()->prepend(__('Vistex Integration'));
        return $resultPage;
    }
}

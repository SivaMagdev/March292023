<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\RegularPrice;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Import controller class
 */
class Import extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $directoryList;

    /**
     * Constructor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
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
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->downloadSampleAction();

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ecomm_PriceEngine::regularprice_import');
        $resultPage->getConfig()->getTitle()->prepend(__('Import Regular Price'));
        return $resultPage;
    }

    public function downloadSampleAction()
    {
        $dir = $this->directoryList;
        // let's get the log dir for instance
        $logDir = $dir->getPath(DirectoryList::VAR_DIR);
        $path = $logDir . '/' . 'import';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $htaccess_file = $path . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $text = "allow from all";
            // Write the contents back to the file
            file_put_contents($htaccess_file, $text);
        }
        //Sample Csv generation Code
        $outputFile = $logDir . '/' . 'import' . '/' . 'regularpriceimport.csv';
        if (!file_exists($outputFile)) {
            $heading = [
                __('SKU'),
                __('NDC'),
                __('Generic Name'),
                __('Strength and Size/Count'),
                __('Pack Size'),
                __('GPO Name'),
                __('GPO Price'),
                __('Dish Price'),
                __('Direct Order Price'),
                __('Start Date'),
                __('End Date'),
                __('GPO Ref#')
            ];
            $handle = fopen($outputFile, 'w');
            fputcsv($handle, $heading);
        }
    }
}

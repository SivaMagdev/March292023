<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Product;

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
        $resultPage->setActiveMenu('Ecomm_PriceEngine::product_import');
        $resultPage->getConfig()->getTitle()->prepend(__('Import and Export Product Masters'));
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
        $outputFile = $logDir . '/' . 'import' . '/' . 'productimport.csv';
        if (!file_exists($outputFile)) {
            $heading = [
                __('SKU'),
                __('Generic Name'),
                __('DEA Class'),
                __('Dosage Form'),
                __('DRL Division'),
                __('Theraputic category'),
                __('Keywords (Search Bar)'),
                __('FDA Rating'),
                __('Brand Name'),
                __('Special Handling and Storage'),
                __('Strength and Size/Count'),
                __('size\count'),
                __('COLOR, SHAPE, MARKINGS '),
                __('CASE PACK'),
                __('Closure'),
                __('Vial size'),
                __('Concentration'),
                __('latex free'),
                __('preservative free'),
                __('gluten free'),
                __('Dye free'),
                __('Bar coded'),
                __('Cap Color'),
                __('New in 2019'),
                __('IMAGE FILE NAME (CMYK/300 DPI)'),
                __('Product Video'),
                __('Product Website'),
                __('Link to Medication Guide on DRL site'),
                __('Link to Prescribing Information on DRL site'),
                __('Daily Med Link to Package Insert'),
                __('CPSIA Info attachment'),
                __('MSDS Sheet'),
                __('COMMENTS'),
                __('Amerisource Bergen (6)'),
                __('Amerisource Bergen (8)'),
                __('Cardinal'),
                __('HD Smith'),
                __('McKesson'),
                __('M & D')
            ];
            $handle = fopen($outputFile, 'w');
            fputcsv($handle, $heading);
        }
    }
}

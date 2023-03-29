<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Product;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Export controller class
 */
class Export extends \Magento\Backend\App\Action
{

    protected $_productCollectionFactory;

    protected $_productFactory;

    protected $directoryList;

    /**
     * Constructor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        DirectoryList $directoryList
    ) {
        $this->_fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productFactory  = $productFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
        parent::__construct($context);
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $name = 'catalog-master-'.date('m-d-Y-H-i-s');
        $filepath = 'export/' .$name. '.csv'; // at Directory path Create a Folder Export and FIle
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        //column name dispay in your CSV

        $columns = ['SKU Code', 'NDC','Generic Name','DEA Class','Dosage Form','Container Type','Theraputic category','Keywords (Search Bar)','FDA Rating','Brand Name','Special Handling and Storage', 'Refrigerated','Total Content','Pack Size','Description','CASE PACK','Closure','Vial size','Concentration','latex free(Yes/NO)','preservative free(Yes/NO)','gluten free(Yes/NO)','Dye free(Yes/NO)','Bar coded(Yes/NO)','Cap Color','Link to Medication Guide on DRL site','Link to Prescribing Information on DRL site','HDMA','MSDS Sheet','Amerisource Bergen (8)','Cardinal','McKesson','Morries & Dicksom','Black Box (Yes/No)','Molecule', 'Molecule Description','Material Description','Dosage','Product Hierarchy','Theraphy','Strength','Pack Size', 'DRD Product Type'];

        foreach ($columns as $column)
        {
            $header[] = $column; //storecolumn in Header array
        }

        $stream->writeCsv($header);

        $mandatory_cols = ['(Mandatory)', '(Mandatory)','(Mandatory)','','(Mandatory)','(Mandatory)','(Mandatory)','','(Mandatory)','','(Mandatory)', '','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','','','','','','','','','','','','','','','','','','','', '','','','','','','',''];

        $stream->writeCsv($mandatory_cols);

        $productCollection = $this->_productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');

        foreach ($productCollection as $product) {
            //echo '<pre>'.print_r($product->getData(), true).'</pre>';

            $itemData = [];

            $_product = $this->_productFactory->create()->load($product->getId());

            //echo $_product->getSku().'<br />';
            $itemData[] = $_product->getMaterial();
            $itemData[] = $_product->getSku();
            $itemData[] = $_product->getName();
            $itemData[] = $_product->getDeaClass();

            $itemData[] = $_product->getAttributeText('dosage_form');
            $itemData[] = $_product->getAttributeText('drl_division');

            $itemData[] = $_product->getAttributeText('theraputic_cat');
            $itemData[] = $_product->getMetaKeyword();
            $itemData[] = $_product->getFdaRating();

            $itemData[] = $_product->getAttributeText('brand_name');
            $itemData[] = $_product->getSpecialHandlingStorage();
            $itemData[] = $_product->getAttributeText('cold_chain');

            $itemData[] = $_product->getAttributeText('strength');
            $itemData[] = $_product->getAttributeText('pack_size');
            $itemData[] = $_product->getShortDescription();
            $itemData[] = $_product->getCasePack();
            $itemData[] = $_product->getClosure();
            $itemData[] = $_product->getVialSize();
            $itemData[] = $_product->getConcentration();
            $itemData[] = $_product->getAttributeText('latex_free');
            $itemData[] = $_product->getAttributeText('preservative_free');
            $itemData[] = $_product->getAttributeText('gluten_free');
            $itemData[] = $_product->getAttributeText('dye_free');
            $itemData[] = $_product->getAttributeText('bar_coded');
            $itemData[] = $_product->getCapColor();
            $itemData[] = $_product->getLinkMedication();
            $itemData[] = $_product->getLinkPrescribing();
            $itemData[] = $_product->getLinkDailymed();
            $itemData[] = $_product->getLinkMsds();
            $itemData[] = $_product->getAmerisourceBergen();
            $itemData[] = $_product->getCardinal();
            $itemData[] = $_product->getMckesson();
            $itemData[] = $_product->getMD();
            $itemData[] = $_product->getAttributeText('black_box');


            $itemData[] = $_product->getMolecule();
            $itemData[] = $_product->getMoleculeDesc();
            $itemData[] = $_product->getMaterialDesc();
            //$itemData[] = $_product->getDivisionCode();
            //$itemData[] = $_product->getDivisionDesc();
            $itemData[] = $_product->getDosageCode();
            //$itemData[] = $_product->getDosageDesc();
            $itemData[] = $_product->getProductHierarchy();
            $itemData[] = $_product->getTheraphyCode();
            //$itemData[] = $_product->getTheraphyDesc();
            $itemData[] = $_product->getStrengthCode();
            $itemData[] = $_product->getPackSizeCode();
            $itemData[] = $_product->getAttributeText('drd_product_type');
            /*$itemData[] = $_product->getSubTheraphyCode();
            $itemData[] = $_product->getSubTheraphyDesc();
            $itemData[] = $_product->getBrandCode();
            $itemData[] = $_product->getBrandDesc();
            $itemData[] = $_product->getCasePackCode();
            $itemData[] = $_product->getScCode();
            $itemData[] = $_product->getScDesc();
            $itemData[] = $_product->getStatusSap();
            $itemData[] = $_product->getSapCreationDate();*/
            $stream->writeCsv($itemData);
        }

        //exit();

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = $name.'.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);

    }

    public function downloadCatalogAction()
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

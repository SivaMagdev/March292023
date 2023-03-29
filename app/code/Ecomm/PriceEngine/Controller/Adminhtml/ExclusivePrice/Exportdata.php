<?php
namespace Ecomm\PriceEngine\Controller\Adminhtml\ExclusivePrice;

use Magento\Framework\App\Filesystem\DirectoryList;

class Exportdata extends \Magento\Backend\App\Action
{
    
    protected $uploaderFactory;

    protected $exclusivePriceFactory; 

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Ecomm\PriceEngine\Model\ExclusivePriceFactory $exclusivePriceFactory


    ) {
       parent::__construct($context);
       $this->_fileFactory = $fileFactory;
       $this->exclusivePriceFactory = $exclusivePriceFactory;
       $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
       parent::__construct($context);
    }

    public function execute()
    {   
    
$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 

$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');


      if (empty($customerIds)) {
        $name = date('m-d-Y-H-i-s');
        $filepath = 'export/export-data-' .$name. '.csv'; // at Directory path Create a Folder Export and FIle
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();


        $columns = ['SKU','NDC','Product Name','Customer','Price','Start Date','End Date'];

            foreach ($columns as $column) 
            {
                $header[] = $column; //storecolumn in Header array
            }

        $stream->writeCsv($header);
        $exclusivePriceFactory=$this->exclusivePriceFactory->create()->getCollection();

         foreach($exclusivePriceFactory as $item){
          $productObj = $productRepository->get($item->getData('ndc'));

            $product_name=$productObj->getName(); 
            $itemData = [];
            $itemData[] = $item->getData('product_sku');
            $itemData[] = $item->getData('ndc');
            $itemData[] = $product_name;
            $itemData[] = $item->getData('customer_id');
            $itemData[] = $item->getData('price');
            $itemData[] = $item->getData('start_date');
            $itemData[] = $item->getData('end_date');
            $stream->writeCsv($itemData);
         

        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'Exclusive-'.$name.'.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
       
     }
}

}
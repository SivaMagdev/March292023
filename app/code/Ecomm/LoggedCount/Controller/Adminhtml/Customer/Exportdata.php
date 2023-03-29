<?php
namespace Ecomm\LoggedCount\Controller\Adminhtml\Customer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Exportdata extends \Magento\Backend\App\Action
{
    
    protected $uploaderFactory;

    protected $loggedCountFactory; 

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Ecomm\LoggedCount\Model\LoggedCountFactory $loggedCountFactory,
         TimezoneInterface $timezone

    ) {
       parent::__construct($context);
       $this->_fileFactory = $fileFactory;
       $this->loggedCountFactory = $loggedCountFactory;
        $this->timezone = $timezone;
       $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
       parent::__construct($context);
    }

    public function execute()
    {   
        $customerIds = $this->getRequest()->getParam('selected'); 

      if (empty($customerIds)) {
        $name = date('m-d-Y-H-i-s');
        $filepath = 'export/export-data-' .$name. '.csv'; // at Directory path Create a Folder Export and FIle
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();


        $columns = ['customer_id','customer_name','company','bill_state','sales_rep','customer_login','year'];

            foreach ($columns as $column) 
            {
                $header[] = $column; //storecolumn in Header array
            }

        $stream->writeCsv($header);
        $productCollectionFactory=$this->loggedCountFactory->create()->getCollection();

         foreach($productCollectionFactory as $item){

            $itemData = [];
            $itemData[] = $item->getData('customer_id');
            $itemData[] = $item->getData('customer_name');
            $itemData[] = $item->getData('company');
            $itemData[] = $item->getData('bill_state');
            $itemData[] = $item->getData('sales_repo');
            $itemData[] = $item->getData('customer_login');
            $itemData[] = $item->getData('year');
            $stream->writeCsv($itemData);

        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'login-frequency-import-'.$name.'.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
       
     }
      else {
        

        $name = date('m-d-Y-H-i-s');
        $filepath = 'export/export-data-' .$name. '.csv'; // at Directory path Create a Folder Export and FIle
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();


        $columns = ['customer_id','customer_name','company','bill_state','sales_rep','customer_login','year'];

            foreach ($columns as $column) 
            {
                $header[] = $column; //storecolumn in Header array
            }

        $stream->writeCsv($header);

        $location_collection = $this->loggedCountFactory->create()->getCollection()->addFieldToFilter('customer_id',['in' => $customerIds]);
         foreach($location_collection as $item){

            $itemData = [];
            $itemData[] = $item->getData('customer_id');
            $itemData[] = $item->getData('customer_name');
            $itemData[] = $item->getData('company');
            $itemData[] = $item->getData('bill_state');
            $itemData[] = $item->getData('sales_repo');
            $itemData[] = $this->timezone->date(new \DateTime($item->getData('customer_login')))->format('Y-m-d H:i:s');
           // $itemData[] = $item->getData('customer_login');
            $itemData[] = $item->getData('year');
            $stream->writeCsv($itemData);
        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'login-frequency-'.$name.'.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);

    }
}

}
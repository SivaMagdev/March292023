<?php
namespace Ecomm\PriceEngine\Controller\Adminhtml\Stock;

use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    public function execute()
    {
        $name = date('m_d_Y_H_i_s');
        $filepath = 'export/stockimport' . $name . '.csv';
        $this->directory->create('export');
        /* Open file */
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $columns = $this->getColumnHeader();
        foreach ($columns as $column) {
            $header[] = $column;
        }
        /* Write Header */
        $stream->writeCsv($header);

        $columns = $this->getColumnMandatory();
        foreach ($columns as $column) {
            $mandatory[] = $column;
        }
        $stream->writeCsv($mandatory);
        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'stockimport.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
    }

    /* Header Columns */
    public function getColumnHeader() {
        $headers = ['SKU','Inventory','Start Date (YYYY-MM-DD)','End Date (YYYY-MM-DD)','Stock Out Thereshold','NDC','Generic Name'];
        return $headers;
    }

    /* Mandatory Columns */
    public function getColumnMandatory() {
        $headers = ['(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','',''];
        return $headers;
    }
}
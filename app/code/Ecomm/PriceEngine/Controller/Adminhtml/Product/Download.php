<?php
namespace Ecomm\PriceEngine\Controller\Adminhtml\Product;

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
        $filepath = 'export/material_master_sample' . $name . '.csv';
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

        $csvfilename = 'material_master_sample_import.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
    }

    /* Header Columns */
    public function getColumnHeader() {
        $headers = $heading = [
                __('SKU Code'),
                __('NDC'),
                __('Generic Name'),
                __('DEA Class'),
                __('Dosage Form'),
                __('Container Type'),
                __('Theraputic category'),
                __('Keywords (Search Bar)'),
                __('FDA Rating'),
                __('Brand Name'),
                __('Special Handling and Storage'),
                __('Refrigerated'),
                __('Total Content'),
                __('Pack Size'),
                __('Description'),
                __('CASE PACK'),
                __('Closure'),
                __('Vial size'),
                __('Concentration'),
                __('Latex free(Yes/NO)'),
                __('Preservative free(Yes/NO)'),
                __('Gluten free(Yes/NO)'),
                __('Dye free(Yes/NO)'),
                __('Bar coded(Yes/NO)'),
                __('Cap Color'),
                __('Link to Medication Guide on DRL site'),
                __('Link to Prescribing Information on DRL site'),
                __('HDMA'),
                __('MSDS Sheet'),
                __('Amerisource Bergen (8)'),
                __('Cardinal'),
                __('McKesson'),
                __('Morries & Dicksom'),
                __('Black Box (Yes/No)'),
                __('Molecule'),
                __('Molecule Description'),
                __('Material description'),
                __('Dosage'),
                __('Product hierarchy'),
                __('Therapy'),
                __('Strength'),
                __('Pack Size'),
                __('DRD Product Type')
            ];
        return $headers;
    }

    /* Mandatory Columns */
    public function getColumnMandatory() {
        $headers = $heading = [
                '(Mandatory)',
                '(Mandatory)',
                '(Mandatory)',
                '',
                '(Mandatory)',
                '(Mandatory)',
                '(Mandatory)',
                '',
                '(Mandatory)',
                '(Mandatory)',
                '(Mandatory)',
                '',
                '(Mandatory)',
                '(Mandatory)',
                '(Mandatory)',
                '(Mandatory)',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ];
        return $headers;

    }
}
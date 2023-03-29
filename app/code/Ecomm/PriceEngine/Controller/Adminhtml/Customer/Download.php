<?php
namespace Ecomm\PriceEngine\Controller\Adminhtml\Customer;

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
        $filepath = 'export/customer_sample_import' . $name . '.csv';
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
        $columns = $this->getMandatoryHeader();
        foreach ($columns as $column) {
            $mheader[] = $column;
        }
        /* Write Header */
        $stream->writeCsv($mheader);
        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'customer_sample_import.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
    }

    /* Header Columns */
    public function getColumnHeader() {
        $headers = $heading = [
                __('Customer Name (BP no.) SAP Code'),
                __('Account group'),
                __('Magento ID'),
                __('Customer First Name'),
                __('Last Name'),
                __('Email'),
                __('Legal Business Name'),
                __('DBA'),
                __('D-U-N-S Number'),
                __('Company Website'),
                __('Corporate Street Address'),
                __('Corporate City'),
                __('Corporate State'),
                __('Corporate ZIP Code'),
                __('Corporate Country'),
                __('Corporate Fax'),
                __('Corporate Phone Number'),
                __('Billing Street Address'),
                __('Billing City'),
                __('Billing State'),
                __('Billing ZIP Code'),
                __('Billing Country'),
                __('Billing Fax'),
                __('Billing Phone Number'),
                __('State License ID'),
                __('State License Expiry Date(YYYY-MM-DD)'),
                __('Shipping Street Address'),
                __('Shipping City'),
                __('Shipping State'),
                __('Shipping  ZIP Code'),
                __('Shipping Country'),
                __('Shipping Fax'),
                __('Shipping Phone number_format(number)'),
                __('DEA Licsense ID'),
                __('DEA  Licsense Expiry Date'),
                __('DEA Street Address'),
                __('DEA City'),
                __('DEA State'),
                __('DEA ZIP Code'),
                __('DEA Country'),
                __('DRL Contact Person E-mail Address'),
                __('Corporate Contact Name'),
                __('Corporate Contact Phone Number'),
                __('Corporate Contact E-mail Address'),
                __('Purchasing Contact Name'),
                __('Purchasing Contact Phone Number'),
                __('Purchasing Contact E-mail Address'),
                __('Accounts Payable Contact Name'),
                __('Accounts Payable Contact Phone Number'),
                __('Accounts Payable E-mail Address'),
                __('EDI Contact Name'),
                __('EDI Contact Phone Number'),
                __('EDI Contact E-mail Address'),
                __('Shipment Contact Name'),
                __('Shipment Contact Phone Number'),
                __('Shipment Contact E-mail Address'),
                __('Type of Business'),
                __('Additional Information on Other type of Business'),
                __('Federal Tax ID'),
                __('GLN Number'),
                __('Please fill the EDI Capabilities'),
                __('GPO Name (Choose from the list: Vizient | TRG | Premier | Intalere | HealthTrust | Others)'),
                __('Expected Monthly Purchases'),
                __('Are you disproportionate hospital(Y/N)'),
                __('IDN Affiliation'),
                __('Trade Reference Business Name'),
                __('Trade Reference Street Address'),
                __('Trade Reference City'),
                __('Trade Reference State/Province'),
                __('Trade Reference ZIP Code'),
                __('Trade Reference Country'),
                __('Trade Reference Fax'),
                __('Trade Reference Phone Number'),
                __('Trade Reference E-mail Address'),
                __('Bank Name'),
                __('Bank Street address'),
                __('Bank City'),
                __('Bank State'),
                __('Bank Country'),
                __('Bank Zip'),
                __('Bank Contact Name'),
                __('Bank Contact Email address'),
                __('Bank Phone Number'),
                __('Bank Fax Number'),
                __('Bank Account Number'),
                __('Company Code'),
                __('Distribution Channel'),
                __('Division'),
                __('Search Terms'),
                __('Address 1/Street 1'),
                __('Address 2/Street 2'),
                __('Street/House No'),
                __('Pin Code'),
                __('City'),
                __('Country'),
                __('Region'),
                __('Ship to party'),
                __('SAP Adrress number'),
                __('Sales district'),
                __('Incoterm'),
                __('Payment terms'),
                __('Incoterm Destination'),
                __('Tax/Tin/VAT/Number'),
                __('Sold to party code'),
                __('Bill to party'),
                __('Payer'),
                __('Ship to party'),
                __('HIN Number')
            ];
        return $headers;
    }

    /* Header Columns */
    public function getMandatoryHeader() {
        $headers = $heading = [
                __('(Mandatory)'),
                __('(Mandatory)'),
                __(''),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __(''),
                __(''),
                __(''),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __(''),
                __(''),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __(''),
                __(''),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __('(Mandatory)'),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __(''),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)'),
                __('(Mandatory)')
            ];
        return $headers;
    }
}
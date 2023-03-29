<?php

/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_VideoList
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\OrderHin\Controller\Adminhtml\Index;

use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Model\CustomerFactory;
use Ecomm\OrderHin\Model\HinDataFactory;

/**
 * Index controller class
 */
class CsvDownload extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    private $fileFactory;
    private $csvProcessor;
    private $directoryList;
    private $hinData;

    /**
     * Constructor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        FileFactory $fileFactory,
        Csv $csvProcessor,
        DirectoryList $directoryList,
        HinDataFactory $hinData
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->fileFactory = $fileFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->hinData = $hinData;
        parent::__construct($context);
    }


    public function execute()
    {
        $fileName = date("Y-m-d").'_order_hin.csv';
        $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            . "/" . $fileName;
    
        $collection = $this->hinData->create()->getCollection();
        $personalData = $this->getPresonalData($collection);
    
        $this->csvProcessor
                ->setDelimiter(';')
                ->setEnclosure('"')
                ->saveData(
                    $filePath,
                    $personalData
                );
    
        return $this->fileFactory->create(
            $fileName,
            [
                'type' => "filename",
                'value' => $fileName,
                'rm' => true,
            ],
               \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/octet-stream'
        );
    }

    protected function getPresonalData( $collection )
    {
        $result = [];
        $collectionData = $collection->getData();
        $result[] = [
            'No',
            'Magento Order Id',
            'SAP Order Id',
            'Legal Name',
            'Ship To Id',
            'Company Name',
            'Member Id',
            'HIN',
            'HIN Status',
           'HIN Start Date',
           'HIN End Date',
            '340b Id',
            '340b Start Date',
            '340b End Date'
        ];
    
        if(!empty($collectionData)){
        foreach ($collectionData as $list) {
            $result[] = [
                $list['entity_id'],
                $list['order_id'],
                $list['sap_order_id'],
                $list['organization_name'],
                $list['sap_address_id'],
                $list['company_name'],
                $list['member_id'],
                $list['hin_id'],
                $list['hin_status'],
                $list['hin_start'],
                $list['hin_end'],
                $list['three_four_b_id'],
                $list['three_four_b_start'],
                $list['three_four_b_end']
            ];
        }}else{
            $result = [];
        }
    
        return $result;
    }
}

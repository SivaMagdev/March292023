<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\ExclusivePrice;

use Magento\Framework\App\Filesystem\DirectoryList;
use Ecomm\PriceEngine\Model\ExclusivePriceFactory;

/**
 * Process controller class
 */
class Process extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $directoryList;

    protected $_exclusivepriceFactory;

    /**
     * Constructor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        DirectoryList $directoryList,
        ExclusivePriceFactory $exclusivepriceFactory,
        \Magento\Framework\File\Csv $csv
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->directoryList = $directoryList;
        $this->_exclusivepriceFactory    = $exclusivepriceFactory;
        $this->csv = $csv;
        parent::__construct($context);
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecomm_PriceEngine::regularprice_import');
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $isPost = $this->getRequest()->isPost();
        try {
            if ($isPost) {

                $file = $_FILES['import_csv_file'];
                //echo '<pre>'.print_r($file, true).'</pre>';
                $data = $this->getRequest()->getParam('import_csv_file');
                $this->_getSession()->setFormData($data);

                $csvProcessor = $this->csv;
                $importProductRawData = $csvProcessor->getData($file['tmp_name']);


                $counter=1;

                $import = 0;

                $not_exists = '';

                foreach ($importProductRawData as $rowIndex => $dataRow) {

                    //echo '<pre>'.print_r($dataRow, true).'</pre>';

                    //if (isset($dataRow[0]) && strcmp('Thana code',trim($data[0])) != 0 && $counter==1) {
                        //$this->messageManager->addError(__('Columns Thana code is not exists in csv file.'));
                        //$this->_redirect('*/*/import');
                        //return;
                    //}

                    if ($counter > 1 && isset($dataRow[0]) && $dataRow[0]!="") {
                        //echo '<pre>'.print_r($dataRow, true).'</pre>';
                        $sku = trim($dataRow[0]);

                        if($sku) {
                            $exclusivepriceModel = $this->_exclusivepriceFactory->create();
                            $exclusivepriceModel->setProductSku($dataRow[0]);
                            $exclusivepriceModel->setNdc($dataRow[1]);
                            $exclusivepriceModel->setName($dataRow[2]);
                            $exclusivepriceModel->setStrengthCount($dataRow[3]);
                            $exclusivepriceModel->setPackSize($dataRow[4]);
                            $exclusivepriceModel->setCustomerId($dataRow[5]);
                            $exclusivepriceModel->setPrice($dataRow[6]);
                            $exclusivepriceModel->setStartDate($dataRow[7]);
                            $exclusivepriceModel->setEndDate($dataRow[8]);
                            $exclusivepriceModel->setContractRef($dataRow[9]);
                            $exclusivepriceModel->setCreatedAt(date('Y-m-d'));
                            $exclusivepriceModel->setDeleted(0);
                            $exclusivepriceModel->save();
                            $import++;
                        } else {
                            $not_exists.='<br />' . __('SKU') . ' <b>"' . $sku . '"</b> ' . __('empty in row.'.$counter);
                        }
                    }
                    $counter++;

                }

                //echo $import;

                if ($import > 0) {
                    $this->messageManager->addSuccess(__('Exclusive Price imported successfully.') . $not_exists);
                } else {
                    $this->messageManager->addError(__('No price imported, data is not correct, check your file.') . $not_exists);
                }

            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/import');
    }
}

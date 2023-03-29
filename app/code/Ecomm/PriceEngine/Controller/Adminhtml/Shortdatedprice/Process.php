<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Shortdatedprice;

use Magento\Framework\App\Filesystem\DirectoryList;
use Ecomm\PriceEngine\Model\ShortdatedpriceFactory;

/**
 * Process controller class
 */
class Process extends \Magento\Backend\App\Action
{
    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $helper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $directoryList;

    protected $_shortdatedpriceFactory;

    /**
     * Constructor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        DirectoryList $directoryList,
        ShortdatedpriceFactory $shortdatedpriceFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\File\Csv $csv
    ) {
        $this->_transportBuilder        = $transportBuilder;
        $this->inlineTranslation        = $inlineTranslation;
        $this->scopeConfig              = $scopeConfig;
        $this->helper                  = $helper;
        $this->resultPageFactory = $resultPageFactory;
        $this->directoryList = $directoryList;
        $this->_shortdatedpriceFactory    = $shortdatedpriceFactory;
        $this->authSession = $authSession;
        $this->csv = $csv;
        parent::__construct($context);
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecomm_PriceEngine::shortdatedprice_import');
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

                $this->inlineTranslation->suspend();

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $sender = [
                    'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                ];

                $templateVars = [];

                $allowed = array('csv');
                $filename = $_FILES['import_csv_file']['name'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!in_array($ext, $allowed)) {
                    $this->messageManager->addError("File type is wrong, please upload file in csv format.");
                    //exit();
                } else {

                    $file = $_FILES['import_csv_file'];
                    //echo '<pre>'.print_r($file, true).'</pre>';
                    $data = $this->getRequest()->getParam('import_csv_file');
                    $this->_getSession()->setFormData($data);

                    $csvProcessor = $this->csv;
                    $importProductRawData = $csvProcessor->getData($file['tmp_name']);

                    $sprices_array = [];
                    $mandatory_columns = [];
                    $headers = [];

                    $mandatory_error = '';


                    $counter=1;

                    $import = 0;

                    $not_exists = '';

                    foreach ($importProductRawData as $rowIndex => $dataRow) {

                        //echo '<pre>'.print_r($dataRow, true).'</pre>';

                        if($counter == 1){
                            $headers = $dataRow;
                        } else if($counter == 2){
                            $mandatory_columns = $dataRow;
                        }

                        if ($counter > 2) {

                            //echo '<pre>'.print_r($mandatory_columns, true).'</pre>'; exit();
                            foreach($dataRow as $idx=>$rowInfo){
                                if($mandatory_columns[$idx] != '' && $rowInfo == ''){
                                    //echo $idx.' - '.$account_group.' - '.$rowInfo.'<br />';
                                    $mandatory_error .= 'On Row '.$counter.' coloumn '.$headers[$idx].' is missing!<br />';

                                }
                            }

                            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$dataRow[8])) {
                                $mandatory_error .= 'On Row '.$counter.' expiry date is in wrong format!<br />';
                            }

                            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$dataRow[9])) {
                                $mandatory_error .= 'On Row '.$counter.' start date is in wrong format!<br />';
                            }

                            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$dataRow[10])) {
                                $mandatory_error .= 'On Row '.$counter.' end date is in wrong format!<br />';
                            }

                            $sprices_array[] = $dataRow;

                        }

                        $counter++;
                    }

                    if($mandatory_error == '') {

                        foreach($sprices_array as $priceRow){
                            //echo '<pre>'.print_r($dataRow, true).'</pre>';
                            $sku = trim($priceRow[0]);

                            if($sku) {
                                //echo $sku .' - '.$priceRow[7].' - '.trim($priceRow[9]).'<br />';
                                $_sdprice = $this->_shortdatedpriceFactory->create()->getCollection()
                                ->addFieldToFilter('product_sku', $sku)
                                ->addFieldToFilter('batch', trim($priceRow[7]))
                                ->addFieldToFilter('start_date', trim($priceRow[9]))
                                ->getFirstItem()->getData();

                                //echo '<pre>'.print_r($_sdprice, true).'</pre>'; exit();

                                //echo $_sdprice['shortdated_price_id'].'<br />';

                                if(!isset($_sdprice['shortdated_price_id'])){

                                    $shortdatedpriceModel = $this->_shortdatedpriceFactory->create();
                                    $shortdatedpriceModel->setProductSku($priceRow[0]);
                                    $shortdatedpriceModel->setNdc($priceRow[1]);
                                    $shortdatedpriceModel->setName($priceRow[2]);
                                    $shortdatedpriceModel->setStrenthCount($priceRow[3]);
                                    $shortdatedpriceModel->setPackSize($priceRow[4]);
                                    $shortdatedpriceModel->setShortdatedPrice($priceRow[5]);
                                    $shortdatedpriceModel->setInventory($priceRow[6]);
                                    $shortdatedpriceModel->setBatch($priceRow[7]);
                                    $shortdatedpriceModel->setExpiryDate($priceRow[8]);
                                    $shortdatedpriceModel->setStartDate($priceRow[9]);
                                    $shortdatedpriceModel->setEndDate($priceRow[10]);
                                    $shortdatedpriceModel->setCreatedBy($this->authSession->getUser()->getUsername());
                                    $shortdatedpriceModel->setCreatedAt(date('Y-m-d'));
                                    $shortdatedpriceModel->setDeleted(0);
                                    $shortdatedpriceModel->save();
                                    $import++;
                                } else {
                                    $shortdatedpriceModel = $this->_shortdatedpriceFactory->create()->load($_sdprice['shortdated_price_id']);
                                    $shortdatedpriceModel->setProductSku($priceRow[0]);
                                    $shortdatedpriceModel->setNdc($priceRow[1]);
                                    $shortdatedpriceModel->setName($priceRow[2]);
                                    $shortdatedpriceModel->setStrenthCount($priceRow[3]);
                                    $shortdatedpriceModel->setPackSize($priceRow[4]);
                                    $shortdatedpriceModel->setShortdatedPrice($priceRow[5]);
                                    $shortdatedpriceModel->setInventory($priceRow[6]);
                                    $shortdatedpriceModel->setBatch($priceRow[7]);
                                    $shortdatedpriceModel->setExpiryDate($priceRow[8]);
                                    $shortdatedpriceModel->setStartDate($priceRow[9]);
                                    $shortdatedpriceModel->setEndDate($priceRow[10]);
                                    $shortdatedpriceModel->setCreatedBy($this->authSession->getUser()->getUsername());
                                    $shortdatedpriceModel->setCreatedAt(date('Y-m-d'));
                                    $shortdatedpriceModel->setDeleted(0);
                                    $shortdatedpriceModel->save();
                                    $import++;
                                }
                            } else {
                                $not_exists.='<br />' . __('SKU') . ' <b>"' . $sku . '"</b> ' . __('empty in row.'.$counter);
                            }
                        }

                        //echo $import;

                        if ($import > 0) {
                            $this->messageManager->addSuccess(__('Short Dated Price imported successfully.') . $not_exists);

                            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                            $to_emails = explode(',', $this->helper->getToEmails());
                            if($to_emails) {
                                $transport =
                                    $this->_transportBuilder
                                    ->setTemplateIdentifier('24') // Send the ID of Email template which is created in Admin panel
                                    ->setTemplateOptions(
                                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                                    )
                                    //->setTemplateVars(['data' => $postObject])
                                    ->setTemplateVars($templateVars)
                                    ->setFrom($sender)
                                    //->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
                                    ->addTo($to_emails)
                                    ->getTransport();
                                $transport->sendMessage();
                            }
                            $this->inlineTranslation->resume();
                        } else {
                            $this->messageManager->addError(__('No price imported, data is not correct, check your file.') . $not_exists);
                        }

                    } else {
                        $this->messageManager->addError($mandatory_error);
                    }
                }

            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/import');
    }
}

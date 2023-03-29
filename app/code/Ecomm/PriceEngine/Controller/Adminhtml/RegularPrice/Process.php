<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\RegularPrice;

use Magento\Framework\App\Filesystem\DirectoryList;
use Ecomm\PriceEngine\Model\RegularPriceFactory;

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

    protected $_regularpriceFactory;

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
        RegularPriceFactory $regularpriceFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\File\Csv $csv
    ) {
        $this->_transportBuilder        = $transportBuilder;
        $this->inlineTranslation        = $inlineTranslation;
        $this->scopeConfig              = $scopeConfig;
        $this->helper                  = $helper;
        $this->resultPageFactory = $resultPageFactory;
        $this->directoryList = $directoryList;
        $this->_regularpriceFactory    = $regularpriceFactory;
        $this->authSession = $authSession;
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

                $this->inlineTranslation->suspend();

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $sender = [
                    'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                ];

                $templateVars = [];$allowed = array('csv');
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

                    $rprices_array = [];
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

                            if($dataRow[11] != ''){
                                if($dataRow[5] == '' || $dataRow[6] == ''){
                                    $mandatory_error .= 'On Row '.$counter.' GPO Price/Dish Price is missing!<br />';
                                }
                            } else {
                                if($dataRow[7] == ''){
                                    $mandatory_error .= 'On Row '.$counter.' Direct Order Price is missing!<br />';
                                }
                            }

                            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$dataRow[9])) {
                                $mandatory_error .= 'On Row '.$counter.' start date is in wrong format!<br />';
                            }

                            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$dataRow[10])) {
                                $mandatory_error .= 'On Row '.$counter.' end date is in wrong format!<br />';
                            }

                            $rprices_array[] = $dataRow;

                        }

                        $counter++;
                    }

                    if($mandatory_error == '') {
                        //echo '<pre>'.print_r($rprices_array, true).'</pre>'; exit();

                        foreach($rprices_array as $priceRow){

                            $sku = trim($priceRow[1]);

                            if($sku) {

                                //echo $sku .' - '.$priceRow[11].' - '.trim($priceRow[9]).'<br />';
                                $_gpoprice = $this->_regularpriceFactory->create()->getCollection()
                                ->addFieldToFilter('ndc', $sku)
                                ->addFieldToFilter('gpo_name', trim($priceRow[11]))
                                ->addFieldToFilter('start_date', trim($priceRow[9]))
                                ->getFirstItem()->getData();

                                //echo '<pre>'.print_r($_gpoprice, true).'</pre>';

                                //echo $_gpoprice['gpo_price_id'].'<br />';
                                if(!isset($_gpoprice['gpo_price_id'])){
                                    $regularpriceModel = $this->_regularpriceFactory->create();
                                    $regularpriceModel->setProductSku(trim($priceRow[0]));
                                    $regularpriceModel->setNdc(trim($priceRow[1]));
                                    $regularpriceModel->setName(trim($priceRow[2]));
                                    $regularpriceModel->setStrengthCount(trim($priceRow[3]));
                                    $regularpriceModel->setPackSize(trim($priceRow[4]));
                                    $regularpriceModel->setGpoPrice(trim($priceRow[5]));
                                    $regularpriceModel->setDishPrice(trim($priceRow[6]));
                                    $regularpriceModel->setDirectPrice(trim($priceRow[7]));
                                    $regularpriceModel->setContractRef(trim($priceRow[8]));
                                    $regularpriceModel->setStartDate(trim($priceRow[9]));
                                    $regularpriceModel->setEndDate(trim($priceRow[10]));
                                    $regularpriceModel->setGpoName(trim($priceRow[11]));
                                    $regularpriceModel->setGpoRef(trim($priceRow[12]));
                                    $regularpriceModel->setCreatedBy($this->authSession->getUser()->getUsername());
                                    $regularpriceModel->setCreatedAt(date('Y-m-d'));
                                    $regularpriceModel->setDeleted(0);
                                    $regularpriceModel->save();
                                    $import++;
                                } else {
                                    $regularpriceModel = $this->_regularpriceFactory->create()->load($_gpoprice['gpo_price_id']);
                                    $regularpriceModel->setProductSku(trim($priceRow[0]));
                                    $regularpriceModel->setNdc(trim($priceRow[1]));
                                    $regularpriceModel->setName(trim($priceRow[2]));
                                    $regularpriceModel->setStrengthCount(trim($priceRow[3]));
                                    $regularpriceModel->setPackSize(trim($priceRow[4]));
                                    $regularpriceModel->setGpoPrice(trim($priceRow[5]));
                                    $regularpriceModel->setDishPrice(trim($priceRow[6]));
                                    $regularpriceModel->setDirectPrice(trim($priceRow[7]));
                                    $regularpriceModel->setContractRef(trim($priceRow[8]));
                                    $regularpriceModel->setStartDate(trim($priceRow[9]));
                                    $regularpriceModel->setEndDate(trim($priceRow[10]));
                                    $regularpriceModel->setGpoName(trim($priceRow[11]));
                                    $regularpriceModel->setGpoRef(trim($priceRow[12]));
                                    $regularpriceModel->setCreatedBy($this->authSession->getUser()->getUsername());
                                    $regularpriceModel->setCreatedAt(date('Y-m-d'));
                                    $regularpriceModel->setDeleted(0);
                                    $regularpriceModel->save();
                                    $import++;
                                }

                            } else {
                                $not_exists.='<br />' . __('SKU') . ' <b>"' . $sku . '"</b> ' . __('empty in row.'.$counter);
                            }

                        }

                        if ($import > 0) {
                            $this->messageManager->addSuccess(__('Regular Price imported successfully.') . $not_exists);

                            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                            $to_emails = explode(',', $this->helper->getToEmails());
                            if($to_emails) {
                                $transport =
                                    $this->_transportBuilder
                                    ->setTemplateIdentifier('23') // Send the ID of Email template which is created in Admin panel
                                    ->setTemplateOptions(
                                        [
                                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                                        ]
                                    )
                                    ->setTemplateVars($templateVars)
                                    ->setFrom($sender)
                                    ->addTo($to_emails)
                                    ->getTransport();
                                $transport->sendMessage();
                            }
                            $this->inlineTranslation->resume();
                        } else {
                            $this->messageManager->addError(__('No price imported, data is not correct (or) data already exist, check your file.') . $not_exists);
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

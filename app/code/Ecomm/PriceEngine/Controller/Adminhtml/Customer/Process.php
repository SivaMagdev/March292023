<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Customer;

use Magento\Framework\App\Filesystem\DirectoryList;

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

    protected $storeManager;

    protected $_groupRepository;

    protected $_customerGroup;

    protected $encryptorInterface;

    protected $customerInterface;

    protected $customerFactory;

    protected $_customerRepository;

    protected $_addressRepository;

    protected $dataAddressFactory;

    protected $_addressConfig;

    protected $_attributeFactory;

    protected $country;

    protected $_regionFactory;

    protected $_eavAttribute;

    protected $_eavConfig;

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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $dataAddressFactory,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Directory\Model\Country $country,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\File\Csv $csv,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->_transportBuilder        = $transportBuilder;
        $this->inlineTranslation        = $inlineTranslation;
        $this->scopeConfig              = $scopeConfig;
        $this->helper                   = $helper;
        $this->resultPageFactory        = $resultPageFactory;
        $this->directoryList            = $directoryList;
        $this->storeManager             = $storeManager;
        $this->_groupRepository         = $groupRepository;
        $this->_customerGroup           = $customerGroup;
        $this->encryptorInterface       = $encryptorInterface;
        $this->customerInterface        = $customerInterface;
        $this->customerFactory          = $customerFactory;
        $this->_customerRepository      = $customerRepository;
        $this->_addressRepository       = $addressRepository;
        $this->dataAddressFactory       = $dataAddressFactory;
        $this->_addressConfig           = $addressConfig;
        $this->_attributeFactory        = $attributeFactory;
        $this->_eavAttribute            = $eavAttribute;
        $this->country                  = $country;
        $this->_regionFactory           = $regionFactory;
        $this->csv                      = $csv;
        $this->_eavConfig               = $eavConfig;
        parent::__construct($context);
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecomm_PriceEngine::customer_import');
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

                $customerGroups = $this->_customerGroup->toOptionArray();
                array_unshift($customerGroups, array('value'=>'', 'label'=>'Any'));
                $customer_group_lists = [];
                foreach($customerGroups as $customerGroup){
                    if($customerGroup['value'] > 0){
                        $customer_group_lists[$customerGroup['value']] = $customerGroup['label'];
                    }
                }
                //echo '<pre>'.print_r($customer_group_lists, true).'</pre>'; exit();

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
                    $data = $this->getRequest()->getParam('import_csv_file');
                    $this->_getSession()->setFormData($data);

                    $csvProcessor = $this->csv;
                    $importCustomerRawData = $csvProcessor->getData($file['tmp_name']);

                    //echo '<pre>'.print_r($importCustomerRawData, true).'</pre>'; exit();

                    $customers_array = [];
                    $mandatory_columns = [];
                    $headers = [];

                    $mandatory_error = '';

                    $counter=1;

                    $regions = $this->_regionFactory->create()->getCollection()->addFieldToFilter('country_id', 'US');
                    $region_list = [];
                    foreach($regions->getData() as $region){

                        $region_list[$region['code']] = $region['region_id'];

                    }

                    //echo '<pre>'.print_r($region_list, true).'</pre>'; exit();

                    foreach ($importCustomerRawData as $rowIndex => $dataRow) {

                        $account_group = '';

                        if($counter == 1){
                            $headers = $dataRow;
                        } else if($counter == 2){
                            $mandatory_columns = $dataRow;
                        }

                        if ($counter > 2) {

                            //echo '<pre>'.print_r($mandatory_columns, true).'</pre>'; exit();
                            foreach($dataRow as $idx=>$rowInfo){

                                if($idx == 1){
                                    $account_group = $rowInfo;
                                } else if($idx == 61 && $account_group == 'ZDSL' && $rowInfo == '') {
                                    // skip validation group
                                    //echo $idx.' - '.$account_group.' - '.$rowInfo.'<br />';
                                } else {
                                    if($mandatory_columns[$idx] != '' && $rowInfo == ''){
                                        //echo $idx.' - '.$account_group.' - '.$rowInfo.'<br />';
                                        $mandatory_error .= 'On Row '.$counter.' column '.$headers[$idx].' is missing!<br />';

                                    }
                                }

                            }

                            //to check whether all the fields are in excel as per customer data count
                            if(count($dataRow) < "108"){
                                $mandatory_error .= 'One or more columns are missing in the customer import excel file. Please check and try again.<br />';
                            }
                            
                            //--------------------RGION VALIDATION---------------------------------

                            if (strlen(trim($dataRow[12])) > 0){
                                if(!isset($region_list[trim($dataRow[12])])){
                                    $mandatory_error .= 'On Row '.$counter.' Corporate State is invalid!<br />';
                                }
                            }

                            if (strlen(trim($dataRow[19])) > 0){
                                if(!isset($region_list[trim($dataRow[19])])){
                                    $mandatory_error .= 'On Row '.$counter.' Billing State is invalid!<br />';
                                }
                            }

                            if (strlen(trim($dataRow[28])) > 0){
                                if(!isset($region_list[trim($dataRow[28])])){
                                    $mandatory_error .= 'On Row '.$counter.' Shipping State is invalid!<br />';
                                }
                            }

                            if (strlen(trim($dataRow[37])) > 0){
                                if(!isset($region_list[trim($dataRow[37])])){
                                    $mandatory_error .= 'On Row '.$counter.' DEA State is invalid!<br />';
                                }
                            }

                            //--------------------RGION VALIDATION---------------------------------

                            if (!filter_var(trim($dataRow[5]), FILTER_VALIDATE_EMAIL)) {
                                $mandatory_error .= 'On Row '.$counter.' Email is invalid!<br />';
                            }

                            if (strlen(trim($dataRow[39])) > 0) {
                                if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$dataRow[9])) {
                                    $mandatory_error .= 'On Row '.$counter.' Company Website is invalid!<br />';
                                }
                            }

                            if (strlen(trim($dataRow[13])) < 5 || strlen(trim($dataRow[13])) > 5) {
                                $mandatory_error .= 'On Row '.$counter.' Corporate Zip Code is invalid!<br />';
                            }

                            if (strlen(trim($dataRow[16])) < 10 || strlen(trim($dataRow[16])) > 10) {
                                $mandatory_error .= 'On Row '.$counter.' Corporate Phone number is invalid!<br />';
                            }

                            if (strlen(trim($dataRow[20])) < 5 || strlen(trim($dataRow[20])) > 5) {
                                $mandatory_error .= 'On Row '.$counter.' Billing Zip Code is invalid!<br />';
                            }

                            if (strlen(trim($dataRow[23])) < 10 || strlen(trim($dataRow[23])) > 10) {
                                $mandatory_error .= 'On Row '.$counter.' Billing Phone number is invalid!<br />';
                            }

                            if (strlen(trim($dataRow[29])) < 5 || strlen(trim($dataRow[29])) > 5) {
                                $mandatory_error .= 'On Row '.$counter.' Shipping Zip Code is invalid!<br />';
                            }

                            if (strlen(trim($dataRow[32])) > 0){
                                if (strlen(trim($dataRow[32])) < 10 || strlen(trim($dataRow[32])) > 10) {
                                    $mandatory_error .= 'On Row '.$counter.' Shipping Phone number is invalid!<br />';
                                }
                            }

                            if(trim($dataRow[38]) != ''){
                                if (strlen(trim($dataRow[38])) < 5 || strlen(trim($dataRow[38])) > 5) {
                                    $mandatory_error .= 'On Row '.$counter.' Shipping Zip Code is invalid!<br />';
                                }
                            }

                            if (strlen(trim($dataRow[42])) < 10 || strlen(trim($dataRow[42])) > 10) {
                                $mandatory_error .= 'On Row '.$counter.' Corporate Phone number is invalid!<br />';
                            }

                            if (!filter_var(trim($dataRow[43]), FILTER_VALIDATE_EMAIL)) {
                                $mandatory_error .= 'On Row '.$counter.' Corporate Contact E-mail is invalid!<br />';
                            }

                            if (strlen(trim($dataRow[45])) < 10 || strlen(trim($dataRow[45])) > 10) {
                                $mandatory_error .= 'On Row '.$counter.' Purchasing Phone number is invalid!<br />';
                            }

                            if (!filter_var(trim($dataRow[46]), FILTER_VALIDATE_EMAIL)) {
                                $mandatory_error .= 'On Row '.$counter.' Purchasing Contact E-mail is invalid!<br />';
                            }

                            if (strlen(trim($dataRow[48])) < 10 || strlen(trim($dataRow[48])) > 10) {
                                $mandatory_error .= 'On Row '.$counter.' Account Payable Phone number is invalid!<br />';
                            }

                            if (!filter_var(trim($dataRow[49]), FILTER_VALIDATE_EMAIL)) {
                                $mandatory_error .= 'On Row '.$counter.' Account Payable Contact E-mail is invalid!<br />';
                            }

                            if(trim($dataRow[51]) != ''){
                                if (strlen(trim($dataRow[51])) < 10 || strlen(trim($dataRow[51])) > 10) {
                                    $mandatory_error .= 'On Row '.$counter.' EDI Contact Phone number is invalid!<br />';
                                }
                            }

                            if(trim($dataRow[52]) != ''){
                                if (!filter_var($dataRow[52], FILTER_VALIDATE_EMAIL)) {
                                    $mandatory_error .= 'On Row '.$counter.' EDI Contact E-mail is invalid!<br />';
                                }
                            }

                            if(trim($dataRow[54]) != ''){
                                if (strlen(trim($dataRow[54])) < 10 || strlen(trim($dataRow[54])) > 10) {
                                    $mandatory_error .= 'On Row '.$counter.' Shipment Phone number is invalid!<br />';
                                }
                            }

                            if(trim($dataRow[55]) != ''){
                                if (!filter_var(trim($dataRow[55]), FILTER_VALIDATE_EMAIL)) {
                                    $mandatory_error .= 'On Row '.$counter.' Shipment Contact E-mail is invalid!<br />';
                                }
                            }

                            if(trim($dataRow[69]) != ''){
                                if (strlen(trim($dataRow[69])) < 5 || strlen(trim($dataRow[69])) > 5) {
                                    $mandatory_error .= 'On Row '.$counter.' Trade Reference Zip Code is invalid!<br />';
                                }
                            }

                            if(trim($dataRow[72]) != ''){
                                if (strlen(trim($dataRow[72])) < 10 || strlen(trim($dataRow[72])) > 10) {
                                    $mandatory_error .= 'On Row '.$counter.' Trade Reference Phone number is invalid!<br />';
                                }
                            }

                            if(trim($dataRow[73]) != ''){
                                if (!filter_var($dataRow[73], FILTER_VALIDATE_EMAIL)) {
                                    $mandatory_error .= 'On Row '.$counter.' Trade Reference E-mail is invalid!<br />';
                                }
                            }

                            /*if (strlen(trim($dataRow[79])) < 5 || strlen(trim($dataRow[79])) > 5) {
                                $mandatory_error .= 'On Row '.$counter.' Bank Zip Code is invalid!<br />';
                            }

                            if (!filter_var($dataRow[81], FILTER_VALIDATE_EMAIL)) {
                                $mandatory_error .= 'On Row '.$counter.' Bank E-mail is invalid!<br />';
                            }

                            if (strlen(trim($dataRow[82])) < 10 || strlen(trim($dataRow[82])) > 10) {
                                $mandatory_error .= 'On Row '.$counter.' Bank Phone number is invalid!<br />';
                            }*/

                            $customers_array[] = $dataRow;

                        }

                        $counter++;

                    }
                    //echo 'mandatory_error: '.$mandatory_error; exit();

                    if($mandatory_error == '') {

                        $partof_organization = $this->getOptionList('customer', 'partof_organization');
                        $business_type = $this->getOptionList('customer', 'business_type');
                        $application_status = $this->getOptionList('customer', 'application_status');
                        //echo '<pre>'.print_r($application_status, true).'</pre>'; exit();

                        $application_status_id = array_search('Pending Approval',$application_status);

                        if($mandatory_error!= ''){
                            $this->messageManager->addError($mandatory_error);
                        } else {
                            $storeId = $this->storeManager->getStore()->getId();
                            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

                            if($customers_array){
                                foreach($customers_array as $customers_data){

                                    if(trim($customers_data[1]) == 'ZDRL'){
                                        //echo '<pre>'.print_r($customers_data, true).'</pre>';

                                        $email = trim($customers_data[5]);

                                        try {
                                            $customer_info = $this->_customerRepository->get($email,$websiteId);
                                            //echo 'customer ID:'.$customer_info->getId();

                                            //$customer = $this->_customerRepository->getById($customer_info->getId());

                                            //echo '<pre>'.print_r($customer_group_lists, true).'</pre>';

                                            //echo $customers_data[61];

                                            $customer_group_id = array_search($customers_data[61], $customer_group_lists);

                                            if($customer_group_id == '' || $customer_group_id <=0){
                                                $customer_group_id = 1;
                                            }

                                            //echo $customer_group_id;

                                            $customer_info->setFirstname($customers_data[3]);
                                            $customer_info->setLastname($customers_data[4]);
                                            $customer_info->setGroupId($customer_group_id);
                                            $customer_info->setCustomAttribute('application_status', $application_status_id);
                                            $customer_info->setCustomAttribute('organization_name', $customers_data[6]);
                                            $customer_info->setCustomAttribute('job_title', $customers_data[6]);

                                            $customer_info->setCustomAttribute('legal_business_name', $customers_data[6]);
                                            $customer_info->setCustomAttribute('dba', $customers_data[7]);
                                            $customer_info->setCustomAttribute('duns_number', $customers_data[8]);
                                            $customer_info->setCustomAttribute('company_website', $customers_data[9]);
                                            $customer_info->setCustomAttribute('contact_person', $customers_data[40]);

                                            $customer_info->setCustomAttribute('corporate_contact_name', $customers_data[41]);
                                            $customer_info->setCustomAttribute('corporate_contact_phone_number', $customers_data[42]);
                                            $customer_info->setCustomAttribute('corporate_contact_email_address', $customers_data[43]);

                                            $customer_info->setCustomAttribute('purchasing_contact_name', $customers_data[44]);
                                            $customer_info->setCustomAttribute('purchasing_contact_phone_number', $customers_data[45]);
                                            $customer_info->setCustomAttribute('purchasing_contact_email_address', $customers_data[46]);

                                            $customer_info->setCustomAttribute('accounts_payable_contact_name', $customers_data[47]);
                                            $customer_info->setCustomAttribute('ap_contact_phone_number', $customers_data[48]);
                                            $customer_info->setCustomAttribute('ap_email_address', $customers_data[49]);

                                            $customer_info->setCustomAttribute('edi_contact_name', $customers_data[50]);
                                            $customer_info->setCustomAttribute('edi_contact_phone', $customers_data[51]);
                                            $customer_info->setCustomAttribute('edi_contact_email', $customers_data[52]);

                                            $customer_info->setCustomAttribute('ship_contact_name', $customers_data[53]);
                                            $customer_info->setCustomAttribute('ship_contact_phone', $customers_data[54]);
                                            $customer_info->setCustomAttribute('ship_contact_email', $customers_data[55]);

                                            $customer_info->setCustomAttribute('business_type', array_search($customers_data[56],$business_type));
                                            $customer_info->setCustomAttribute('business_other', $customers_data[57]);
                                            $customer_info->setCustomAttribute('federal_taxid', $customers_data[58]);
                                            $customer_info->setCustomAttribute('gln_no', $customers_data[59]);
                                            $customer_info->setCustomAttribute('edi_capabilities', 0);
                                            $customer_info->setCustomAttribute('fill_edi_capabilities', $customers_data[60]);
                                            $customer_info->setCustomAttribute('idn_affiliation', $customers_data[64]);
                                            $customer_info->setCustomAttribute('partof_organization', array_search($customers_data[61],$partof_organization));
                                            //$customer_info->setCustomAttribute('gpo_others', $customers_data[4]);
                                            $disproportionate_hospital = 0;
                                            if(strtolower(trim($customers_data[63])) == 'y') { $disproportionate_hospital = 1; }
                                            $customer_info->setCustomAttribute('disproportionate_hospital', $disproportionate_hospital);
                                            $customer_info->setCustomAttribute('monthly_purchase', $customers_data[62]);

                                            $customer_info->setCustomAttribute('company_street', $customers_data[10]);
                                            $customer_info->setCustomAttribute('company_city', $customers_data[11]);
                                            $customer_info->setCustomAttribute('company_state', $customers_data[12]);
                                            $customer_info->setCustomAttribute('company_zip', $customers_data[13]);
                                            $customer_info->setCustomAttribute('company_country', (trim($customers_data[14]) == '') ? 'USA' : $customers_data[14]);
                                            $customer_info->setCustomAttribute('company_fax', $customers_data[15]);
                                            $customer_info->setCustomAttribute('company_phone', $customers_data[16]);

                                            $customer_info->setCustomAttribute('trade_businessname', $customers_data[65]);
                                            $customer_info->setCustomAttribute('trade_address', $customers_data[66]);
                                            $customer_info->setCustomAttribute('trade_city', $customers_data[67]);
                                            $customer_info->setCustomAttribute('trade_state', $customers_data[68]);
                                            $customer_info->setCustomAttribute('trade_zip', $customers_data[69]);
                                            $customer_info->setCustomAttribute('trade_country', $customers_data[70]);
                                            $customer_info->setCustomAttribute('trade_fax', $customers_data[71]);
                                            $customer_info->setCustomAttribute('trade_phone', $customers_data[72]);
                                            $customer_info->setCustomAttribute('trade_email', $customers_data[73]);

                                            $customer_info->setCustomAttribute('bank_name', $customers_data[74]);
                                            $customer_info->setCustomAttribute('bank_address', $customers_data[75]);
                                            $customer_info->setCustomAttribute('bank_city', $customers_data[76]);
                                            $customer_info->setCustomAttribute('bank_state', $customers_data[77]);
                                            $customer_info->setCustomAttribute('bank_country', $customers_data[78]);
                                            $customer_info->setCustomAttribute('bank_zip', $customers_data[79]);
                                            $customer_info->setCustomAttribute('bank_contactname', $customers_data[80]);
                                            $customer_info->setCustomAttribute('bank_email', $customers_data[81]);
                                            $customer_info->setCustomAttribute('bank_phone', $customers_data[82]);
                                            $customer_info->setCustomAttribute('bank_fax', $customers_data[83]);
                                            $customer_info->setCustomAttribute('bank_account', $customers_data[84]);

                                            $customer_info->setCustomAttribute('federal_taxid', $customers_data[58]);
                                            $customer_info->setTaxvat($customers_data[102]);

                                            $customer_info->setCustomAttribute('sap_customer_id', $customers_data[0]);
                                            $customer_info->setCustomAttribute('sap_company_code', $customers_data[85]);
                                            $customer_info->setCustomAttribute('sap_distribution_channel', $customers_data[86]);
                                            $customer_info->setCustomAttribute('sap_division', $customers_data[87]);
                                            $customer_info->setCustomAttribute('sap_search_terms', $customers_data[88]);
                                            $customer_info->setCustomAttribute('sap_sales_district', $customers_data[98]);
                                            $customer_info->setCustomAttribute('sap_incoterm', $customers_data[99]);
                                            $customer_info->setCustomAttribute('sap_payment_terms', $customers_data[100]);
                                            $customer_info->setCustomAttribute('sap_incoterm_destination', $customers_data[101]);
                                            $customer_info->setCustomAttribute('sap_sold_to_party', $customers_data[103]);
                                            $customer_info->setCustomAttribute('sap_payer', $customers_data[105]);

                                            $billing_address_data = [];

                                            $billing_address_data['customer_id'] = $customer_info->getId();
                                            $billing_address_data['first_name'] = $customers_data[3];
                                            $billing_address_data['last_name'] = $customers_data[4];
                                            $billing_address_data['organization_name'] = $customers_data[6];
                                            $billing_address_data['street'] = $customers_data[17];
                                            $billing_address_data['city'] = $customers_data[18];
                                            $billing_address_data['state'] = $region_list[trim($customers_data[19])];
                                            $billing_address_data['zip'] = $customers_data[20];
                                            $billing_address_data['country'] = (trim($customers_data[21]) == '') ? 'US' : $customers_data[21];
                                            $billing_address_data['fax'] = $customers_data[22];
                                            $billing_address_data['phone'] = $customers_data[23];
                                            $billing_address_data['sap_address_code'] = $customers_data[104];

                                            $shipping_address_data = [];
                                            $shipping_address_data['customer_id'] = $customer_info->getId();
                                            $shipping_address_data['first_name'] = $customers_data[3];
                                            $shipping_address_data['last_name'] = $customers_data[4];
                                            $shipping_address_data['organization_name'] = $customers_data[6];
                                            $shipping_address_data['state_license_id'] = $customers_data[24];

                                            $shipping_address_data['hin_id'] = $customers_data[107];

                                            $shipping_address_data['state_license_expiry'] = $customers_data[25];
                                            $shipping_address_data['street'] = $customers_data[26];
                                            $shipping_address_data['city'] = $customers_data[27];
                                            $shipping_address_data['state'] = $region_list[trim($customers_data[28])];
                                            $shipping_address_data['zip'] = $customers_data[29];
                                            $shipping_address_data['country'] = (trim($customers_data[30]) == '') ? 'US' : $customers_data[30];
                                            $shipping_address_data['fax'] = $customers_data[31];
                                            $shipping_address_data['phone'] = (trim($customers_data[32]) == '') ? $customers_data[23] : $customers_data[32];
                                            $shipping_address_data['sap_address_code'] = $customers_data[106];

                                            $dea_address_data = [];
                                            if(trim($customers_data[33]) != '') {
                                                $dea_address_data['customer_id'] = $customer_info->getId();
                                                $dea_address_data['first_name'] = $customers_data[3];
                                                $dea_address_data['last_name'] = $customers_data[4];
                                                $dea_address_data['organization_name'] = $customers_data[6];
                                                $dea_address_data['dea_license_id'] = $customers_data[33];
                                                $dea_address_data['dea_license_expiry'] = $customers_data[34];
                                                $dea_address_data['street'] = $customers_data[35];
                                                $dea_address_data['city'] = $customers_data[36];
                                                $dea_address_data['state'] = $region_list[trim($customers_data[37])];
                                                $dea_address_data['zip'] = $customers_data[38];
                                                $dea_address_data['country'] = $customers_data[39];
                                                $dea_address_data['fax'] = '';
                                                $dea_address_data['phone'] = (trim($customers_data[32]) == '') ? $customers_data[23] : $customers_data[32];
                                            }

                                            if ($customer_info->getAddresses()) {

                                                //echo '<pre>'.print_r($billing_address_data, true).'</pre>';
                                                $billingAddressId = $customer_info->getDefaultBilling();
                                                $shippingAddressId = $customer_info->getDefaultShipping();

                                                if($billingAddressId != 0) {
                                                    $this->updateAddress($billingAddressId, $billing_address_data, $is_default_billing=1, $is_default_shipping=0);
                                                }

                                                if($shippingAddressId != 0 && ($billingAddressId != $shippingAddressId)) {
                                                    $this->updateAddress($shippingAddressId, $shipping_address_data, $is_default_billing=0, $is_default_shipping=1);
                                                }

                                            } else {
                                                $this->createAddress($billing_address_data, $is_default_billing=1, $is_default_shipping=0);
                                                $this->createAddress($shipping_address_data, $is_default_billing=0, $is_default_shipping=1);
                                                if($dea_address_data) {
                                                    $this->createAddress($dea_address_data, $is_default_billing=0, $is_default_shipping=0);
                                                }
                                            }

                                            $this->_customerRepository->save($customer_info);



                                        } catch (\Exception $e) {
                                            //echo $e->getMessage();
                                            //throw new \Magento\Framework\Exception\LocalizedException(__("The customer email isn't defined."));
                                            //echo $storeId.'-'.$websiteId;

                                            $customer_info = $this->customerInterface->create();

                                            $customer_group_id = array_search($customers_data[61], $customer_group_lists);

                                            if($customer_group_id == '' || $customer_group_id <=0){
                                                $customer_group_id = 1;
                                            }

                                            $customer_info->setWebsiteId($websiteId);
                                            $customer_info->setEmail(trim($email));
                                            $customer_info->setFirstname($customers_data[3]);
                                            $customer_info->setLastname($customers_data[4]);
                                            $customer_info->setGroupId($customer_group_id);
                                            $customer_info->setCustomAttribute('organization_name', $customers_data[6]);
                                            $customer_info->setCustomAttribute('job_title', $customers_data[6]);

                                            $customer_info->setCustomAttribute('application_status', $application_status_id);

                                            $customer_info->setCustomAttribute('legal_business_name', $customers_data[6]);
                                            $customer_info->setCustomAttribute('dba', $customers_data[7]);
                                            $customer_info->setCustomAttribute('duns_number', $customers_data[8]);
                                            $customer_info->setCustomAttribute('company_website', $customers_data[9]);
                                            $customer_info->setCustomAttribute('contact_person', $customers_data[40]);

                                            $customer_info->setCustomAttribute('corporate_contact_name', $customers_data[41]);
                                            $customer_info->setCustomAttribute('corporate_contact_phone_number', $customers_data[42]);
                                            $customer_info->setCustomAttribute('corporate_contact_email_address', $customers_data[43]);

                                            $customer_info->setCustomAttribute('purchasing_contact_name', $customers_data[44]);
                                            $customer_info->setCustomAttribute('purchasing_contact_phone_number', $customers_data[45]);
                                            $customer_info->setCustomAttribute('purchasing_contact_email_address', $customers_data[46]);

                                            $customer_info->setCustomAttribute('accounts_payable_contact_name', $customers_data[47]);
                                            $customer_info->setCustomAttribute('ap_contact_phone_number', $customers_data[48]);
                                            $customer_info->setCustomAttribute('ap_email_address', $customers_data[49]);

                                            $customer_info->setCustomAttribute('edi_contact_name', $customers_data[50]);
                                            $customer_info->setCustomAttribute('edi_contact_phone', $customers_data[51]);
                                            $customer_info->setCustomAttribute('edi_contact_email', $customers_data[52]);

                                            $customer_info->setCustomAttribute('ship_contact_name', $customers_data[53]);
                                            $customer_info->setCustomAttribute('ship_contact_phone', $customers_data[54]);
                                            $customer_info->setCustomAttribute('ship_contact_email', $customers_data[55]);

                                            $customer_info->setCustomAttribute('business_type', array_search($customers_data[56],$business_type));
                                            $customer_info->setCustomAttribute('business_other', $customers_data[57]);
                                            $customer_info->setCustomAttribute('federal_taxid', $customers_data[58]);
                                            $customer_info->setTaxvat($customers_data[102]);
                                            $customer_info->setCustomAttribute('gln_no', $customers_data[59]);
                                            $customer_info->setCustomAttribute('edi_capabilities', 0);
                                            $customer_info->setCustomAttribute('fill_edi_capabilities', $customers_data[60]);
                                            $customer_info->setCustomAttribute('idn_affiliation', $customers_data[64]);
                                            $customer_info->setCustomAttribute('partof_organization', array_search($customers_data[61],$partof_organization));
                                            //$customer_info->setCustomAttribute('gpo_others', $customers_data[4]);
                                            $disproportionate_hospital = 0;
                                            if(strtolower(trim($customers_data[63])) == 'y') { $disproportionate_hospital = 1; }
                                            $customer_info->setCustomAttribute('disproportionate_hospital', $disproportionate_hospital);
                                            $customer_info->setCustomAttribute('monthly_purchase', $customers_data[62]);

                                            $customer_info->setCustomAttribute('company_street', $customers_data[10]);
                                            $customer_info->setCustomAttribute('company_city', $customers_data[11]);
                                            $customer_info->setCustomAttribute('company_state', $customers_data[12]);
                                            $customer_info->setCustomAttribute('company_zip', $customers_data[13]);
                                            $customer_info->setCustomAttribute('company_country', (trim($customers_data[14]) == '') ? 'USA' : $customers_data[14]);
                                            $customer_info->setCustomAttribute('company_fax', $customers_data[15]);
                                            $customer_info->setCustomAttribute('company_phone', $customers_data[16]);

                                            $customer_info->setCustomAttribute('trade_businessname', $customers_data[65]);
                                            $customer_info->setCustomAttribute('trade_address', $customers_data[66]);
                                            $customer_info->setCustomAttribute('trade_city', $customers_data[67]);
                                            $customer_info->setCustomAttribute('trade_state', $customers_data[68]);
                                            $customer_info->setCustomAttribute('trade_zip', $customers_data[69]);
                                            $customer_info->setCustomAttribute('trade_country', $customers_data[70]);
                                            $customer_info->setCustomAttribute('trade_fax', $customers_data[71]);
                                            $customer_info->setCustomAttribute('trade_phone', $customers_data[72]);
                                            $customer_info->setCustomAttribute('trade_email', $customers_data[73]);

                                            $customer_info->setCustomAttribute('bank_name', $customers_data[74]);
                                            $customer_info->setCustomAttribute('bank_address', $customers_data[75]);
                                            $customer_info->setCustomAttribute('bank_city', $customers_data[76]);
                                            $customer_info->setCustomAttribute('bank_state', $customers_data[77]);
                                            $customer_info->setCustomAttribute('bank_country', $customers_data[78]);
                                            $customer_info->setCustomAttribute('bank_zip', $customers_data[79]);
                                            $customer_info->setCustomAttribute('bank_contactname', $customers_data[80]);
                                            $customer_info->setCustomAttribute('bank_email', $customers_data[81]);
                                            $customer_info->setCustomAttribute('bank_phone', $customers_data[82]);
                                            $customer_info->setCustomAttribute('bank_fax', $customers_data[83]);
                                            $customer_info->setCustomAttribute('bank_account', $customers_data[84]);

                                            $customer_info->setCustomAttribute('taxvat', $customers_data[102]);

                                            $customer_info->setCustomAttribute('sap_customer_id', $customers_data[0]);
                                            $customer_info->setCustomAttribute('sap_company_code', $customers_data[85]);
                                            $customer_info->setCustomAttribute('ap_distribution_channel', $customers_data[86]);
                                            $customer_info->setCustomAttribute('sap_division', $customers_data[87]);
                                            $customer_info->setCustomAttribute('sap_search_terms', $customers_data[88]);
                                            $customer_info->setCustomAttribute('sap_sales_district', $customers_data[98]);
                                            $customer_info->setCustomAttribute('sap_incoterm', $customers_data[99]);
                                            $customer_info->setCustomAttribute('sap_payment_terms', $customers_data[100]);
                                            $customer_info->setCustomAttribute('sap_incoterm_destination', $customers_data[101]);
                                            $customer_info->setCustomAttribute('sap_sold_to_party', $customers_data[103]);
                                            $customer_info->setCustomAttribute('sap_payer', $customers_data[105]);

                                            $hashedPassword = $this->encryptorInterface->getHash('MyNewPass', true);

                                            $this->_customerRepository->save($customer_info, $hashedPassword);

                                            $customer_info = $this->_customerRepository->get($email,$websiteId);
                                            //echo 'customer ID:'.$customer_info->getId();

                                            $billing_address_data = [];
                                            $billing_address_data['customer_id'] = $customer_info->getId();
                                            $billing_address_data['first_name'] = $customers_data[3];
                                            $billing_address_data['last_name'] = $customers_data[4];
                                            $billing_address_data['organization_name'] = $customers_data[6];
                                            $billing_address_data['street'] = $customers_data[17];
                                            $billing_address_data['city'] = $customers_data[18];
                                            $billing_address_data['state'] = $region_list[trim($customers_data[19])];
                                            $billing_address_data['zip'] = $customers_data[20];
                                            $billing_address_data['country'] = (trim($customers_data[21]) == '') ? 'US' : $customers_data[21];
                                            $billing_address_data['fax'] = $customers_data[22];
                                            $billing_address_data['phone'] = $customers_data[23];
                                            $billing_address_data['sap_address_code'] = $customers_data[104];

                                            $shipping_address_data = [];
                                            $shipping_address_data['customer_id'] = $customer_info->getId();
                                            $shipping_address_data['first_name'] = $customers_data[3];
                                            $shipping_address_data['last_name'] = $customers_data[4];
                                            $shipping_address_data['organization_name'] = $customers_data[6];
                                            $shipping_address_data['state_license_id'] = $customers_data[24];

                                            $shipping_address_data['hin_id'] = $customers_data[107];
                                            $shipping_address_data['state_license_expiry'] = $customers_data[25];
                                            $shipping_address_data['street'] = $customers_data[26];
                                            $shipping_address_data['city'] = $customers_data[27];
                                            $shipping_address_data['state'] = $region_list[trim($customers_data[28])];
                                            $shipping_address_data['zip'] = $customers_data[29];
                                            $shipping_address_data['country'] = (trim($customers_data[30]) == '') ? 'US' : trim($customers_data[30]);
                                            $shipping_address_data['fax'] = $customers_data[31];
                                            $shipping_address_data['phone'] = (trim($customers_data[32]) == '') ? $customers_data[23] : $customers_data[32];
                                            $shipping_address_data['sap_address_code'] = $customers_data[106];

                                            $this->createAddress($billing_address_data, $is_default_billing=1, $is_default_shipping=0);
                                            $this->createAddress($shipping_address_data, $is_default_billing=0, $is_default_shipping=1);

                                            $dea_address_data = [];
                                            if(trim($customers_data[33]) != '' && trim($customers_data[24]) == '') {

                                                $region_code = 'IN';
                                                if(isset($region_list[$customers_data[37]]) && $customers_data[37] != '') {
                                                    $region_code = $customers_data[37];
                                                }

                                                $dea_address_data['customer_id'] = $customer_info->getId();
                                                $dea_address_data['first_name'] = $customers_data[3];
                                                $dea_address_data['last_name'] = $customers_data[4];
                                                $dea_address_data['organization_name'] = $customers_data[6];
                                                $dea_address_data['dea_license_id'] = $customers_data[33];
                                                $dea_address_data['dea_license_expiry'] = $customers_data[34];
                                                $dea_address_data['street'] = $customers_data[35];
                                                $dea_address_data['city'] = $customers_data[36];
                                                $dea_address_data['state'] = $region_list[$region_code];
                                                $dea_address_data['zip'] = $customers_data[38];
                                                $dea_address_data['country'] = $customers_data[39];
                                                $dea_address_data['fax'] = '';
                                                $dea_address_data['phone'] = (trim($customers_data[32]) == '') ? $customers_data[23] : $customers_data[32];

                                                $this->createAddress($dea_address_data, $is_default_billing=0, $is_default_shipping=0);
                                            }

                                        }

                                    } else {

                                        $email = trim($customers_data[5]);

                                        try {


                                            $customer_info = $this->_customerRepository->get($email,$websiteId);
                                            //echo 'customer ID:'.$customer_info->getId();

                                            $customer = $this->customerFactory->create();
                                            $customer->setWebsiteId($websiteId);
                                            $customerModel = $customer->load($customer_info->getId());

                                            $default_address = [];
                                            $default_address[] = $customer_info->getDefaultBilling();
                                            $default_address[] = $customer_info->getDefaultShipping();

                                            //echo '<pre>'.print_r($default_address, true).'</pre>'; exit();

                                            $exist_state_licences = [];
                                            $exist_dea_licences = [];
                                            $exist_addresses = [];
                                            if ($customerModel->getAddresses() != null)
                                            {
                                                foreach ($customerModel->getAddresses() as $address) {
                                                    if(!in_array($address->getId(), $default_address)) {
                                                        if($address->getCustomAttribute('state_license_id')){
                                                            $exist_addresses[$address->getId()]['id']['id'] = $address->getId();
                                                            $exist_addresses[$address->getId()]['id']['state_license_id'] = $address->getCustomAttribute('state_license_id')->getValue();
                                                            $exist_addresses[$address->getId()]['id'] = $address->getId();
                                                            $exist_state_licences[$address->getId()]= $address->getCustomAttribute('state_license_id')->getValue();
                                                        }

                                                        if($address->getCustomAttribute('dea_license_id')){
                                                            $exist_dea_licences[$address->getId()]= $address->getCustomAttribute('dea_license_id')->getValue();
                                                        }
                                                    }
                                                }
                                            }

                                            //echo '<pre>'.print_r($exist_dea_licences, true).'</pre>';

                                            $shipping_address_data = [];
                                            $shipping_address_data['customer_id'] = $customer_info->getId();
                                            $shipping_address_data['first_name'] = $customers_data[3];
                                            $shipping_address_data['last_name'] = $customers_data[4];
                                            $shipping_address_data['organization_name'] = $customers_data[6];
                                            $shipping_address_data['state_license_id'] = $customers_data[24];
                                            $shipping_address_data['hin_id'] = $customers_data[107];
                                            $shipping_address_data['state_license_expiry'] = $customers_data[25];
                                            $shipping_address_data['street'] = $customers_data[26];
                                            $shipping_address_data['city'] = $customers_data[27];
                                            $shipping_address_data['state'] = $region_list[trim($customers_data[28])];
                                            $shipping_address_data['zip'] = $customers_data[29];
                                            $shipping_address_data['country'] = (trim($customers_data[30]) == '') ? 'US' : $customers_data[30];
                                            $shipping_address_data['fax'] = $customers_data[31];
                                            $shipping_address_data['phone'] = (trim($customers_data[32]) == '') ? $customers_data[23] : $customers_data[32];
                                            $shipping_address_data['sap_address_code'] = $customers_data[106];

                                            //echo '<pre>'.print_r($shipping_address_data, true).'</pre>';

                                            if(!in_array($shipping_address_data['state_license_id'], $exist_state_licences)) {
                                                $this->createAddress($shipping_address_data, $is_default_billing=0, $is_default_shipping=0);
                                            } else {

                                                $AddressId = array_search($shipping_address_data['state_license_id'], $exist_state_licences);
                                                //echo 'false'.'-'.$AddressId;
                                                $this->updateAddress($AddressId, $shipping_address_data, $is_default_billing=0, $is_default_shipping=0);
                                            }

                                            $dea_address_data = [];
                                            if(trim($customers_data[33]) != '' && trim($customers_data[24]) == '') {

                                                $region_code = 'IN';
                                                if(isset($region_list[$customers_data[37]]) && $customers_data[37] != '') {
                                                    $region_code = $customers_data[37];
                                                }
                                                $dea_address_data['customer_id'] = $customer_info->getId();
                                                $dea_address_data['first_name'] = $customers_data[3];
                                                $dea_address_data['last_name'] = $customers_data[4];
                                                $dea_address_data['organization_name'] = $customers_data[6];
                                                $dea_address_data['dea_license_id'] = $customers_data[33];
                                                $dea_address_data['dea_license_expiry'] = $customers_data[34];
                                                $dea_address_data['street'] = $customers_data[35];
                                                $dea_address_data['city'] = $customers_data[36];
                                                $dea_address_data['state'] = $region_list[trim($customers_data[37])];
                                                $dea_address_data['zip'] = $customers_data[38];
                                                $dea_address_data['country'] = $customers_data[39];
                                                $dea_address_data['fax'] = '';
                                                $dea_address_data['phone'] = (trim($customers_data[32]) == '') ? $customers_data[23] : $customers_data[32];

                                                if(!in_array($dea_address_data['dea_license_id'], $exist_dea_licences)) {$this->createAddress($dea_address_data, $is_default_billing=0, $is_default_shipping=0);
                                                } else {

                                                    $AddressId = array_search($shipping_address_data['dea_license_id'], $exist_dea_licences);
                                                    //echo 'false'.'-'.$AddressId;
                                                    $this->updateAddress($AddressId, $dea_address_data, $is_default_billing=0, $is_default_shipping=0);
                                                }
                                            }
                                        } catch (\Exception $e) {

                                            $this->messageManager->addError($e->getMessage());

                                        }


                                    }

                                }
                            }

                            //exit();

                            try {

                                $templateVars = [];

                                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                                $to_emails = explode(',', $this->helper->getToEmails());
                                if($to_emails) {
                                    $transport =
                                        $this->_transportBuilder
                                        ->setTemplateIdentifier('33') // Send the ID of Email template which is created in Admin panel
                                        ->setTemplateOptions(
                                            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                                        )
                                        //->setTemplateVars(['data' => $postObject])
                                        ->setTemplateVars($templateVars)
                                        ->setFrom($sender)
                                        //->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
                                        ->addTo($to_emails)
                                        //->addCc('maideen.i@gmail.com')
                                        ->getTransport();
                                    $transport->sendMessage();
                                }
                                $this->inlineTranslation->resume();
                            } catch (\Exception $e) {

                                echo 'Test 2:'.$e->getMessage();

                                $this->messageManager->addError($e->getMessage());

                            }

                            $this->messageManager->addSuccess(__('Customer Master imported successfully.'));

                        }
                    } else {
                        $this->messageManager->addError($mandatory_error);
                    }
                }

            }
        } catch (\Exception $e) {
            echo 'Test1:'.$e->getMessage();
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/import');
    }

    private function updateAddress($address_id, $data, $is_default_billing=0, $is_default_shipping=0){

        //echo '<pre>'.print_r($data, true).'</pre>'.'-'.$is_default_billing.'-'.$is_default_shipping;

        $address = $this->_addressRepository->getById($address_id);

        $street[] = $data['street'];

        $address->setFirstname($data['first_name']);
        $address->setLastname($data['last_name']);
        $address->setStreet($street);
        $address->setCity($data['city']);
        $address->setRegionId($data['state']);
        $address->setCountryId($data['country']); // if Customer country is USA then need add state / province
        $address->setPostcode($data['zip']);
        $address->setTelephone($data['phone']);
        $address->setFax($data['fax']);
        $address->setCompany($data['organization_name']);


        if(isset($data['state_license_id'])){
            $address->setCustomAttribute('state_license_id', $data['state_license_id']);
        }

        if(isset($data['hin_id'])){
            $address->setCustomAttribute('hin_id', $data['hin_id']);
        }

        if(isset($data['state_license_expiry'])){
            $address->setCustomAttribute('state_license_expiry', $data['state_license_expiry']);
        }

        if(isset($data['dea_license_id'])){
            $address->setCustomAttribute('dea_license_id', $data['dea_license_id']);
        }

        if(isset($data['dea_license_expiry'])){
            $address->setCustomAttribute('dea_license_expiry', $data['dea_license_expiry']);
        }

        if(isset($data['sap_address_code'])){
            $address->setCustomAttribute('sap_address_code', $data['sap_address_code']);
        }

        if($is_default_billing == 1) {
            $address->setIsDefaultBilling('1');
        } else {
            $address->setIsDefaultBilling('0');
        }

        if($is_default_shipping == 1){
            $address->setIsDefaultShipping('1');
        } else {
            $address->setIsDefaultShipping('0');
        }

        $this->_addressRepository->save($address);

    }

    private function createAddress($data, $is_default_billing=0, $is_default_shipping=0){

        $set_address = $this->dataAddressFactory->create();

        $set_address->setCustomerId($data['customer_id']);

        $street[] = $data['street'];

        $set_address->setFirstname($data['first_name']);
        $set_address->setLastname($data['last_name']);
        $set_address->setStreet($street);
        $set_address->setCity($data['city']);
        $set_address->setRegionId($data['state']);
        $set_address->setCountryId($data['country']); // if Customer country is USA then need add state / province
        $set_address->setPostcode($data['zip']);
        $set_address->setTelephone($data['phone']);
        $set_address->setFax($data['fax']);
        $set_address->setCompany($data['organization_name']);


        if(isset($data['state_license_id'])){
            $set_address->setCustomAttribute('state_license_id', $data['state_license_id']);
        }

        if(isset($data['hin_id'])){
            $set_address->setCustomAttribute('hin_id', $data['hin_id']);
        }

        if(isset($data['state_license_expiry'])){
            $set_address->setCustomAttribute('state_license_expiry', $data['state_license_expiry']);
        }
        if(isset($data['dea_license_id'])){
            $set_address->setCustomAttribute('dea_license_id', $data['dea_license_id']);
        }

        if(isset($data['dea_license_expiry'])){
            $set_address->setCustomAttribute('dea_license_expiry', $data['dea_license_expiry']);
        }

        if(isset($data['sap_address_code'])){
            $set_address->setCustomAttribute('sap_address_code', $data['sap_address_code']);
        }

        if($is_default_billing == 1){
            $set_address->setIsDefaultBilling('1');
        }

        if($is_default_shipping == 1){
            $set_address->setIsDefaultShipping('1');
        }

        $this->_addressRepository->save($set_address);

    }

    private function getOptionList($attribute_type, $attribute_code){
        $option_lists = [];

        //$attribute = $this->_eavConfig->getAttribute('customer', 'partof_organization');
        $attribute = $this->_eavConfig->getAttribute($attribute_type, $attribute_code);
        $options = $attribute->getSource()->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $option_lists[$option['value']] = $option['label'];
            }
        }

        return $option_lists;
    }
}

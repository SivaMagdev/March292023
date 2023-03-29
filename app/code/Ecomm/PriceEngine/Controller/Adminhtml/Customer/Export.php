<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Customer;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Export controller class
 */
class Export extends \Magento\Backend\App\Action
{

    protected $_groupRepository;

    protected $_customerRepository;

    protected $_addressRepository;

    protected $_addressConfig;

    protected $directoryList;

    protected $_eavConfig;

    /**
     * Constructor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Address\Config $addressConfig,
        DirectoryList $directoryList,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->_fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->_groupRepository         = $groupRepository;
        $this->_customerRepository      = $customerRepository;
        $this->_addressRepository       = $addressRepository;
        $this->_addressConfig           = $addressConfig;
        $this->_eavConfig               = $eavConfig;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
        parent::__construct($context);
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $customerIds = $this->getRequest()->getParam('selected');

        if (!is_array($customerIds) || empty($customerIds)) {
            $customerIds = $this->customerSearch($this->getRequest()->getParam('search'));
        }

        if (!is_array($customerIds) || empty($customerIds)) {
            $this->messageManager->addErrorMessage(__('Please select Customer(s).'));
        } else {
            try {
                $name = 'customer-master-'.date('m-d-Y-H-i-s');
                $filepath = 'export/' .$name. '.csv'; // at Directory path Create a Folder Export and FIle
                $this->directory->create('export');

                $stream = $this->directory->openFile($filepath, 'w+');
                $stream->lock();

                $partof_organization = $this->getOptionList('customer', 'partof_organization');
                $business_type = $this->getOptionList('customer', 'business_type');

                //column name dispay in your CSV
                $columns = ['Customer Name (BP no.) SAP Code','Account group','Magento ID','Customer First Name','Last Name','Email','Legal Business Name','DBA','D-U-N-S Number','Company Website','Corporate Street Address ','Corporate City','Corporate State','Corporate ZIP Code ','Corporate Country','Corporate Fax','Corporate Phone Number','Billing Street Address','Billing City','Billing State','Billing ZIP Code','Billing Country','Billing Fax','Billing Phone Number','State License ID','State License Expiry Date(YYYY-MM-DD)','Shipping Street Address','Shipping City','Shipping State','Shipping ZIP Code','Shipping Country','Shipping Fax','Shipping Phone Number','DEA Licsense ID','DEA  Licsense Expiry Date','DEA Street Address','DEA City','DEA State','DEA  ZIP Code','DEA Country','DRL Contact Person','Corporate Contact Name','Corporate Contact Phone Number','Corporate Contact E-mail Address','Purchasing Contact Name','Purchasing Contact Phone Number','Purchasing Contact E-mail Address','Accounts Payable Contact Name','Accounts Payable Contact Phone Number','Accounts Payable E-mail Address','EDI Contact Name','EDI Contact Phone Number','EDI Contact E-mail Address','Shipment Contact Name','Shipment Contact Phone Number','Shipment Contact E-mail Address','Type of Business',' Additional Information on Other type of Business','Federal Tax ID','GLN Number','Please fill the EDI Capabilities','GPO Name','Expected Monthly Purchases','Are you disproportionate hospital(Y/N)','IDN Affiliation','Trade Reference Business Name','Trade Reference Street Address','Trade Reference City','Trade Reference State/Province','Trade Reference ZIP Code','Trade Reference Country','Trade Reference Fax','Trade Reference Phone Number','Trade Reference E-mail Address','Bank Name','Bank Street address','Bank City','Bank State','Bank Country','Bank Zip','Bank Contact Name','Bank Contact Email address','Bank Phone Number','Bank Fax Number','Bank Account Number','Company Code','Distribution Channel','Division','Search Terms','Address 1/Street 1','Address 2/Street 2','Street/House No','Pin Code','City','Country','Region','Ship to party','SAP Adrress number','Sales district','Incoterm','Payment terms','Incoterm Destination','Tax/Tin/VAT/Number','Sold to party code','Bill to party','Payer','Ship to party'];

                foreach ($columns as $column) {
                    $header[] = $column; //storecolumn in Header array
                }
                $stream->writeCsv($header);
                $columns = ['(Mandatory)','(Mandatory)','','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','','','','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','','','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','','','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','','','','','','','','','','','','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)','','','','','','','','','','','','(Mandatory)  (Choose from the list:  Vizient | TRG | Premier | Intalere | HealthTrust | Others)','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','(Mandatory)','(Mandatory)','(Mandatory)','(Mandatory)'];

                foreach ($columns as $column) {
                    $mheader[] = $column; //storecolumn in Header array
                }

                $stream->writeCsv($mheader);

                //echo '<pre>-'.print_r($customerIds, true).'</pre>'; exit();

                foreach ($customerIds as $customer_id) {

                    $itemData = [];

                    $default_address_ids = [];

                    $_customer= $this->_customerRepository->getById($customer_id);

                    $billingAddressId = $_customer->getDefaultBilling();
                    //echo $billingAddressId.'<br />';
                    $default_address_ids[] = $billingAddressId;

                    $shippingAddressId = $_customer->getDefaultShipping();
                    //echo $shippingAddressId.'<br />';
                    $default_address_ids[] = $shippingAddressId;

                    $billing_address_street = '';
                    $billing_address_postal_code = '';
                    $billing_address_city = '';
                    $billing_address_country = '';
                    $billing_address_state = '';
                    $billing_address_telephone = '';
                    $bill_to_party = '';

                    if($billingAddressId) {
                        $billing_address = $this->_addressRepository->getById($billingAddressId);

                        $billing_address_street = implode(', ',$billing_address->getStreet());
                        $billing_address_postal_code = $billing_address->getPostcode();
                        $billing_address_city = $billing_address->getCity();
                        $billing_address_country = $billing_address->getCountryId();
                        $billing_address_state = $billing_address->getRegion()->getRegionCode();
                        $billing_address_telephone = $billing_address->getTelephone();
                        if (!empty($billing_address->getCustomAttribute("sap_address_code"))) {
                            $bill_to_party = $billing_address->getCustomAttribute("sap_address_code")->getValue();
                        }
                    }

                    $shipping_address_street = '';
                    $shipping_address_postal_code = '';
                    $shipping_address_city = '';
                    $shipping_address_country = '';
                    $shipping_address_state = '';
                    $shipping_address_telephone = '';
                    $shipping_address_state_license = '';
                    $shipping_address_state_license_expiry = '';
                    $shipping_address_dea_license = '';
                    $shipping_address_dea_license_expiry = '';
                    $ship_to_party = '';

                    if($shippingAddressId) {
                        $shipping_address = $this->_addressRepository->getById($shippingAddressId);

                        $shipping_address_street = implode(', ',$shipping_address->getStreet());
                        $shipping_address_postal_code = $shipping_address->getPostcode();
                        $shipping_address_city = $shipping_address->getCity();
                        $shipping_address_country = $shipping_address->getCountryId();
                        $shipping_address_state = $shipping_address->getRegion()->getRegionCode();
                        $shipping_address_telephone = $shipping_address->getTelephone();
                        if (!empty($shipping_address->getCustomAttribute("state_license_id"))) {
                            $shipping_address_state_license = $shipping_address->getCustomAttribute("state_license_id")->getValue();
                        }
                        if (!empty($shipping_address->getCustomAttribute("state_license_expiry"))) {
                            $shipping_address_state_license_expiry = $shipping_address->getCustomAttribute("state_license_expiry")->getValue();
                        }
                        if (!empty($shipping_address->getCustomAttribute("dea_license_id"))) {
                            $shipping_address_dea_license = $shipping_address->getCustomAttribute("dea_license_id")->getValue();
                        }
                        if (!empty($shipping_address->getCustomAttribute("dea_license_expiry"))) {
                            $shipping_address_dea_license_expiry = $shipping_address->getCustomAttribute("dea_license_expiry")->getValue();
                        }
                        if (!empty($shipping_address->getCustomAttribute("sap_address_code"))) {
                            $ship_to_party = $shipping_address->getCustomAttribute("sap_address_code")->getValue();
                        }
                    }

                    $sap_customer_id = '';
                    if (!empty($_customer->getCustomAttribute("sap_customer_id"))) {
                        $sap_customer_id = $_customer->getCustomAttribute("sap_customer_id")->getValue();
                    }

                    $inc = 0;

                    $itemData[$inc] = $sap_customer_id; $inc++;
                    $itemData[$inc] = 'ZDRL'; $inc++;
                    $itemData[$inc] = $_customer->getId(); $inc++;
                    $itemData[$inc] = $_customer->getFirstName(); $inc++;
                    $itemData[$inc] = $_customer->getLastName(); $inc++;
                    $itemData[$inc] = $_customer->getEmail(); $inc++;
                    if (!empty($_customer->getCustomAttribute("organization_name"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("organization_name")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("dba"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("dba")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("duns_number"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("duns_number")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("company_website"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("company_website")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("company_street"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("company_street")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("company_city"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("company_city")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("company_state"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("company_state")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("company_zip"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("company_zip")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("company_country"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("company_country")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("company_fax"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("company_fax")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("company_phone"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("company_phone")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    $itemData[$inc] = $billing_address_street; $inc++;
                    $itemData[$inc] = $billing_address_city; $inc++;
                    $itemData[$inc] = $billing_address_state; $inc++;
                    $itemData[$inc] = $billing_address_postal_code; $inc++;
                    $itemData[$inc] = $billing_address_country; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = $billing_address_telephone; $inc++;
                    $itemData[$inc] = $shipping_address_state_license; $inc++;
                    $itemData[$inc] = $shipping_address_state_license_expiry; $inc++;
                    $itemData[$inc] = $shipping_address_street; $inc++;
                    $itemData[$inc] = $shipping_address_city; $inc++;
                    $itemData[$inc] = $shipping_address_state; $inc++;
                    $itemData[$inc] = $shipping_address_postal_code; $inc++;
                    $itemData[$inc] = $shipping_address_country; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = $shipping_address_telephone; $inc++;
                    $itemData[$inc] = $shipping_address_dea_license; $inc++;
                    $itemData[$inc] = $shipping_address_dea_license_expiry; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = ''; $inc++;

                    if (!empty($_customer->getCustomAttribute("contact_person"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("contact_person")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("corporate_contact_name"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("corporate_contact_name")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("corporate_contact_phone_number"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("corporate_contact_phone_number")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("corporate_contact_email_address"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("corporate_contact_email_address")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("purchasing_contact_name"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("purchasing_contact_name")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("purchasing_contact_phone_number"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("purchasing_contact_phone_number")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("purchasing_contact_email_address"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("purchasing_contact_email_address")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("accounts_payable_contact_name"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("accounts_payable_contact_name")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("ap_contact_phone_number"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("ap_contact_phone_number")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("ap_email_address"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("ap_email_address")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("edi_contact_name"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("edi_contact_name")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("edi_contact_phone"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("edi_contact_phone")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("edi_contact_email"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("edi_contact_email")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("ship_contact_name"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("ship_contact_name")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("ship_contact_phone"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("ship_contact_phone")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("ship_contact_email"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("ship_contact_email")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("business_type"))) {
                        $itemData[$inc] = ($_customer->getCustomAttribute("business_type")->getValue() != 0)?$business_type[$_customer->getCustomAttribute("business_type")->getValue()]:'';
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("business_other"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("business_other")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("federal_taxid"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("federal_taxid")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("gln_no"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("gln_no")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("fill_edi_capabilities"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("fill_edi_capabilities")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("partof_organization"))) {
                        //$itemData[$inc] = $_customer->getCustomAttribute("partof_organization")->getValue(); // Dropdown
                        $itemData[$inc] = $partof_organization[$_customer->getCustomAttribute("partof_organization")->getValue()]; // Dropdown
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("monthly_purchase"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("monthly_purchase")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("disproportionate_hospital"))) {
                        if($_customer->getCustomAttribute("disproportionate_hospital")->getValue() == 0){
                            $itemData[$inc] = 'No';
                        } else {
                            $itemData[$inc] = 'Yes';
                        }
                    } else {
                        $itemData[$inc] = 'No';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("idn_affiliation"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("idn_affiliation")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("trade_businessname"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("trade_businessname")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("trade_address"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("trade_address")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("trade_city"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("trade_city")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("trade_state"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("trade_state")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("trade_zip"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("trade_zip")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("trade_country"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("trade_country")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("trade_fax"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("trade_fax")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("trade_phone"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("trade_phone")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("trade_email"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("trade_email")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("bank_name"))) {
                        //$itemData[$inc] = $_customer->getCustomAttribute("bank_name")->getValue();
                        $itemData[$inc] = '';
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("bank_address"))) {
                        //$itemData[$inc] = $_customer->getCustomAttribute("bank_address")->getValue();
                        $itemData[$inc] = '';
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("bank_city"))) {
                        //$itemData[$inc] = $_customer->getCustomAttribute("bank_city")->getValue();
                        $itemData[$inc] = '';
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("bank_state"))) {
                        //$itemData[$inc] = $_customer->getCustomAttribute("bank_state")->getValue();
                        $itemData[$inc] = '';
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("bank_country"))) {
                        //$itemData[$inc] = $_customer->getCustomAttribute("bank_country")->getValue();
                        $itemData[$inc] = '';
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("bank_zip"))) {
                        //$itemData[$inc] = $_customer->getCustomAttribute("bank_zip")->getValue();
                        $itemData[$inc] = '';
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("bank_contactname"))) {
                        //$itemData[$inc] = $_customer->getCustomAttribute("bank_contactname")->getValue();
                        $itemData[$inc] = '';
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("bank_email"))) {
                        //$itemData[$inc] = $_customer->getCustomAttribute("bank_email")->getValue();
                        $itemData[$inc] = '';
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("bank_phone"))) {
                        //$itemData[$inc] = $_customer->getCustomAttribute("bank_phone")->getValue();
                        $itemData[$inc] = '';
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("bank_fax"))) {
                        //$itemData[$inc] = $_customer->getCustomAttribute("bank_fax")->getValue();
                        $itemData[$inc] = '';
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("bank_account"))) {
                        //$itemData[$inc] = $_customer->getCustomAttribute("bank_account")->getValue();
                        $itemData[$inc] = '';
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("sap_company_code"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("sap_company_code")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("sap_distribution_channel"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("sap_distribution_channel")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("sap_division"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("sap_division")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("sap_search_terms"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("sap_search_terms")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    $itemData[$inc] = ''; $inc++;
                    if (!empty($_customer->getCustomAttribute("sap_sales_district"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("sap_sales_district")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("sap_incoterm"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("sap_incoterm")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("sap_payment_terms"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("sap_payment_terms")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("sap_incoterm_destination"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("sap_incoterm_destination")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    $itemData[$inc] = $_customer->getTaxvat(); $inc++;
                    if (!empty($_customer->getCustomAttribute("sap_sold_to_party"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("sap_sold_to_party")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    $itemData[$inc] = $bill_to_party;
                    $inc++;
                    if (!empty($_customer->getCustomAttribute("sap_payer"))) {
                        $itemData[$inc] = $_customer->getCustomAttribute("sap_payer")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;
                    $itemData[$inc] = $ship_to_party;

                    $stream->writeCsv($itemData);

                    if ($_customer->getAddresses()) {
                        foreach($_customer->getAddresses() as $caddress){
                            if(!in_array($caddress->getId(), $default_address_ids)){
                                $shipping_address_street = '';
                                $shipping_address_postal_code = '';
                                $shipping_address_city = '';
                                $shipping_address_country = '';
                                $shipping_address_state = '';
                                $shipping_address_telephone = '';
                                $shipping_address_state_license = '';
                                $shipping_address_state_license_expiry = '';
                                $shipping_address_dea_license = '';
                                $shipping_address_dea_license_expiry = '';
                                $ship_to_party = '';


                                $shipping_address = $this->_addressRepository->getById($caddress->getId());

                                $shipping_address_street = implode(', ',$shipping_address->getStreet());
                                $shipping_address_postal_code = $shipping_address->getPostcode();
                                $shipping_address_city = $shipping_address->getCity();
                                $shipping_address_country = $shipping_address->getCountryId();
                                $shipping_address_state = $shipping_address->getRegion()->getRegionCode();
                                $shipping_address_telephone = $shipping_address->getTelephone();
                                if (!empty($shipping_address->getCustomAttribute("state_license_id"))) {
                                    $shipping_address_state_license = $shipping_address->getCustomAttribute("state_license_id")->getValue();
                                }
                                if (!empty($shipping_address->getCustomAttribute("state_license_expiry"))) {
                                    $shipping_address_state_license_expiry = $shipping_address->getCustomAttribute("state_license_expiry")->getValue();
                                }
                                if (!empty($shipping_address->getCustomAttribute("dea_license_id"))) {
                                    $shipping_address_dea_license = $shipping_address->getCustomAttribute("dea_license_id")->getValue();
                                }
                                if (!empty($shipping_address->getCustomAttribute("dea_license_expiry"))) {
                                    $shipping_address_dea_license_expiry = $shipping_address->getCustomAttribute("dea_license_expiry")->getValue();
                                }
                                if (!empty($shipping_address->getCustomAttribute("sap_address_code"))) {
                                    $ship_to_party = $shipping_address->getCustomAttribute("sap_address_code")->getValue();
                                }

                                $itemData[1] = 'ZDSL';
                                if($shipping_address_state_license == '' && $shipping_address_dea_license!= ''){
                                    $itemData[24] = '';
                                    $itemData[25] = '';
                                    $itemData[26] = '';
                                    $itemData[27] = '';
                                    $itemData[28] = '';
                                    $itemData[29] = '';
                                    $itemData[30] = '';
                                    $itemData[31] = '';
                                    $itemData[32] = '';
                                    $itemData[33] = $shipping_address_dea_license;
                                    $itemData[34] = $shipping_address_dea_license_expiry;
                                    $itemData[35] = $shipping_address_street;
                                    $itemData[36] = $shipping_address_city;
                                    $itemData[37] = $shipping_address_state;
                                    $itemData[38] = $shipping_address_postal_code;
                                    $itemData[39] = $shipping_address_country;
                                } else {
                                    $itemData[24] = $shipping_address_state_license;
                                    $itemData[25] = $shipping_address_state_license_expiry;
                                    $itemData[26] = $shipping_address_street;
                                    $itemData[27] = $shipping_address_city;
                                    $itemData[28] = $shipping_address_state;
                                    $itemData[29] = $shipping_address_postal_code;
                                    $itemData[30] = $shipping_address_country;
                                    $itemData[31] = '';
                                    $itemData[32] = $shipping_address_telephone;
                                    $itemData[33] = $shipping_address_dea_license;
                                    $itemData[34] = $shipping_address_dea_license_expiry;
                                }

                                $itemData[106] = $ship_to_party;

                                //echo '<pre>'.print_r($itemData, true).'</pre>';
                                $stream->writeCsv($itemData);
                            }
                        }
                    }
                }

                //exit();

                $content = [];
                $content['type'] = 'filename'; // must keep filename
                $content['value'] = $filepath;
                $content['rm'] = '1'; //remove csv from var folder

                $csvfilename = $name.'.csv';
                return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

    }

    private function customerSearch($search_keyword) {
        //echo $search_keyword.'<br />';
        $customer_ids = [];
        $customerFactory = $this->_objectManager->get('\Magento\Customer\Model\CustomerFactory');
        $collection = $customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                //->addAttributeToFilter('sponsor_id', array('eq' => $this->_customerSession->getCustomer()->getId()))
                ->addAttributeToFilter(
                        array(
                            array('attribute' => 'firstname', 'like' => '%' . $search_keyword . '%'),
                            array('attribute' => 'lastname', 'like' => '%' . $search_keyword . '%'),
                            array('attribute' => 'email', 'like' => '%' . $search_keyword . '%')
                        ))->load();
        $customers = $collection->getData();
        foreach($customers as $customer){
            $customer_ids[] = $customer['entity_id'];
        }
        //echo json_encode($customer);

        return $customer_ids;
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

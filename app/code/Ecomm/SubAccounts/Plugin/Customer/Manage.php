<?php

namespace Ecomm\SubAccounts\Plugin\Customer;

use Psr\Log\LoggerInterface;

class Manage
{

    protected $customer;

    protected $_eavConfig;

    protected $customerModel;

    protected $customerFactory;

    protected $_addressRepository;

    protected $dataAddressFactory;


    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_general/email';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $storeManager;

    protected $_escaper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customer,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $dataAddressFactory,
        \Magento\Eav\Model\Config $eavConfig,
        LoggerInterface $logger,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Escaper $escaper
    )
    {
        $this->customer = $customer;
        $this->_eavConfig = $eavConfig;
        $this->customerRepository = $customerRepository;
        $this->customerModel=$customerModel;
        $this->customerResourceFactory = $customerFactory;
        $this->_addressRepository       = $addressRepository;
        $this->dataAddressFactory       = $dataAddressFactory;
        $this->logger               = $logger;


        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_escaper = $escaper;
    }
    //public function afterExecute(\Magento\Company\Controller\Customer\Manage $subject, $result)
    public function afterCreate(\Magento\Company\Model\Action\SaveCustomer $subject, $result)
    {

      

        // print_r($subject);
       $newCustomerId = $result->getId();
       

       $sessioncustomer = $this->customer;
       $sessionCustomerData = $sessioncustomer->getData();
       $parent_id = $sessionCustomerData['customer_id'];
      // $parent_id = $sessioncustomer->getId();

       $parent_customer = $this->customerRepository->getById($parent_id);

       $billingAddressId = $parent_customer->getDefaultBilling();
       $shippingAddressId = $parent_customer->getDefaultShipping();

       //$this->logger->info('Address ID:'.$billingAddressId.'-'.$shippingAddressId);

       $this->createAddress($newCustomerId, $billingAddressId, 1, 0);
       $this->createAddress($newCustomerId, $shippingAddressId, 0, 1);

        $custom_attributes = ['organization_name','job_title','legal_business_name','dba','duns_number','company_website','contact_person','federal_taxid','gln_no','edi_capabilities','company_street','company_city','company_state','company_zip','company_country','company_fax','company_phone','bank_name','bank_address','bank_city','bank_state','bank_country','bank_zip','bank_contactname','bank_email','bank_phone','bank_fax','bank_account','sap_customer_id','sap_company_code','sap_distribution_channel','sap_division','sap_search_terms','sap_sales_district','sap_incoterm','sap_payment_terms','sap_incoterm_destination','sap_sold_to_party','sap_payer','trade_businessname','trade_phone','trade_email','trade_address','trade_city','trade_state','trade_zip','trade_fax','trade_country','business_type','business_other','edi_contact_name','edi_contact_phone','edi_contact_email','fill_edi_capabilities','idn_affiliation','partof_organization','gpo_others','disproportionate_hospital','monthly_purchase','corporate_contact_name','corporate_contact_phone_number','corporate_contact_email_address','purchasing_contact_name','purchasing_contact_phone_number','purchasing_contact_email_address','accounts_payable_contact_name','ap_contact_phone_number','ap_email_address','ship_contact_name','ship_contact_phone','ship_contact_email'];

        $new_customer = $this->customerRepository->getById($newCustomerId);

        $attribute = $this->_eavConfig->getAttribute('customer', 'application_status');
        $options = $attribute->getSource()->getAllOptions();

        $application_statuses = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $application_statuses[$option['value']] = $option['label'];
            }
        }

        //$pending_approval_option_id = array_search("Pending Approval",$application_statuses);
        $pending_approval_option_id = array_search("Approved",$application_statuses);
        

        $new_customer->setCustomAttribute('application_status',$pending_approval_option_id);

        $this->customerRepository->save($new_customer);

       
      
        $organization_name = '';

        foreach($custom_attributes as $custom)
        {
            if($parent_customer->getCustomAttribute($custom)) {

                if($custom == 'organization_name')
                {
                    $organization_name = $parent_customer->getCustomAttribute('organization_name')->getValue();
                }

                $new_customer = $this->customerRepository->getById($newCustomerId);
                $new_customer->setCustomAttribute($custom, $parent_customer->getCustomAttribute($custom)->getValue());
                $this->customerRepository->save($new_customer);
            }
        }


        
        

        //send email to admin
        $this->inlineTranslation->suspend();
        try 
        {
                $error = false;                

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;           

                $sender = [
                    'name' => $this->scopeConfig->getValue(
                        'trans_email/ident_sales/name',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ),
                    'email' => $this->scopeConfig->getValue(
                        'trans_email/ident_sales/email',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ),
                    ];
              
                
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($sender);   

                $templateVars=[
                    "customer_name"=>$this->_escaper->escapeHtml($new_customer->getFirstName()),
                    "customer_email"=>$this->_escaper->escapeHtml($new_customer->getEmail()),
                    "customer_company_name"=>$organization_name
                ];

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                $helper = $objectManager->create('Ecomm\Notification\Helper\Data'); 
                $emailhelper = $objectManager->create('Ecomm\CustomContactus\Helper\Email');
                $admintemplate = $emailhelper->getConfigValue('subaccounts/adminemail/subaccounts_admin_notification');               
        
                $admin_email = explode(",",$helper->getToEmails());    
                $transport = 
                $this->_transportBuilder
                ->setTemplateIdentifier($admintemplate) // Send the ID of Email template which is created in Admin panel
                ->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                )
                //->setTemplateVars(['data' => $postObject])
                ->setFrom($sender)
                ->setTemplateVars(
                    $templateVars
                )
                ->addTo($admin_email)
                ->getTransport();
                $transport->sendMessage(); ;
                $this->inlineTranslation->resume();



                $this->inlineTranslation->suspend();
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($sender);

                $templateVars = [];

                $templateVars = [
                    'customer_name' => $new_customer->getFirstName(),
                ];

                $customer_email = array($new_customer->getEmail());


                $transport =
                    $this->_transportBuilder
                    ->setTemplateIdentifier('8') // Send the ID of Email template which is created in Admin panel
                    ->setTemplateOptions(
                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                    )
                    ->setTemplateVars($templateVars)
                    ->setFrom($sender)
                    ->addTo($customer_email)
                    //->addCc('ishita.sarkar@pwc.com')
                    ->getTransport();
                $transport->sendMessage();

                $this->inlineTranslation->resume();

                // Send Email to Admin
                $this->inlineTranslation->suspend();
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

                //echo $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope);
                //echo $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope);

               
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($sender);

                $templateVars = [];

                $templateVars = [
                    'customer_name' => $new_customer->getFirstName(),
                    'customer_email' => $new_customer->getEmail(),
                ];

                $to_emails = explode(",",$helper->getToEmails());   
                //$to_emails = explode(',', $this->_helper->getToEmails());

                $transport =
                    $this->_transportBuilder
                    ->setTemplateIdentifier('9') // Send the ID of Email template which is created in Admin panel
                    ->setTemplateOptions(
                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                    )
                    ->setTemplateVars($templateVars)
                    ->setFrom($sender)
                    ->addTo($to_emails)
                    //->addCc('ishita.sarkar@pwc.com')
                    ->getTransport();
                $transport->sendMessage();

                $this->inlineTranslation->resume();
        
        
        } 
        catch (\Exception $e) 
        {
            \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug($e->getMessage());
            //echo $e->getMessage();
           // die();
           
        }

        return $new_customer;

    }

    private function createAddress($customer_id, $parent_address_id, $is_default_billing=0, $is_default_shipping=0){

        $address = $this->_addressRepository->getById($parent_address_id);

        $set_address = $this->dataAddressFactory->create();

        $set_address->setCustomerId($customer_id);
        $set_address->setFirstname($address->getFirstName());
        $set_address->setLastname($address->getLastName());
        $set_address->setStreet($address->getStreet());
        $set_address->setCity($address->getCity());
        $set_address->setRegionId($address->getRegionId());
        $set_address->setCountryId($address->getCountryId()); // if Customer country is USA then need add state / province
        $set_address->setPostcode($address->getPostcode());
        $set_address->setTelephone($address->getTelephone());
        $set_address->setFax($address->getFax());
        $set_address->setCompany($address->getCompany());

        //$this->logger->info('DEA Expiry:'.$address->getCustomAttribute('dea_license_expiry'));


        if($address->getCustomAttribute('address_status')){
            $set_address->setCustomAttribute('address_status',$address->getCustomAttribute('address_status')->getValue());
        }

        if($address->getCustomAttribute('state_license_status')){
            $set_address->setCustomAttribute('state_license_status',$address->getCustomAttribute('state_license_status')->getValue());
        }

        if($address->getCustomAttribute('state_license_id')){
            $set_address->setCustomAttribute('state_license_id',$address->getCustomAttribute('state_license_id')->getValue());
        }

        if($address->getCustomAttribute('state_license_expiry')){
            $set_address->setCustomAttribute('state_license_expiry', $address->getCustomAttribute('state_license_expiry')->getValue());
        }

        if($address->getCustomAttribute('dea_license_status')){
            $set_address->setCustomAttribute('dea_license_status',$address->getCustomAttribute('dea_license_status')->getValue());
        }

        if($address->getCustomAttribute('dea_license_id')){
            $set_address->setCustomAttribute('dea_license_id', $address->getCustomAttribute('dea_license_id')->getValue());
        }

        if($address->getCustomAttribute('dea_license_expiry')){
            $set_address->setCustomAttribute('dea_license_expiry', $address->getCustomAttribute('dea_license_expiry')->getValue());
        }

        if($address->getCustomAttribute('sap_address_code')){
            $set_address->setCustomAttribute('sap_address_code', $address->getCustomAttribute('sap_address_code')->getValue());
        }

        if($is_default_billing == 1){
            $set_address->setIsDefaultBilling('1');
        }

        if($is_default_shipping == 1){
            $set_address->setIsDefaultShipping('1');
        }

        $this->_addressRepository->save($set_address);

    }
}
?>
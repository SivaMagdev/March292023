<?php

namespace Ecomm\Register\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;
use Magento\Contact\Model\ConfigInterface;
use Ecomm\BellNotification\Helper\BellNotification;
use Ecomm\BellNotification\Helper\PushNotification;

class CustomerSaveAfter implements ObserverInterface
{

    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $storeManager;

    protected $bellNotificationHelper;

    protected $pushNotification;

    protected $_escaper;

    protected $customerRepository;

    protected $_eavConfig;

    protected $companyInterface;

    protected $companyRepository;

    protected $companyObj;

    protected $dataObj;

    protected $_helper;

    protected $companyManagement;

    protected $companyCreditRepository;

    protected $companyPaymentMethodResource;

    protected $companyPaymentMethodFactory;

    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        BellNotification $bellNotificationHelper,
        PushNotification $pushNotification,
        \Magento\Framework\Escaper $escaper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Company\Api\Data\CompanyInterface $companyInterface,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\Company\Model\Company $companyObj,
        \Magento\Framework\Api\DataObjectHelper $dataObj,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $companyCreditRepository,
        \Magento\CompanyPayment\Model\ResourceModel\CompanyPaymentMethod $companyPaymentMethodResource,
        \Magento\CompanyPayment\Model\CompanyPaymentMethodFactory $companyPaymentMethodFactory
    ) {
        $this->_transportBuilder            = $transportBuilder;
        $this->inlineTranslation            = $inlineTranslation;
        $this->scopeConfig                  = $scopeConfig;
        $this->storeManager                 = $storeManager;
        $this->bellNotificationHelper       = $bellNotificationHelper;
        $this->pushNotification             = $pushNotification;
        $this->_escaper                     = $escaper;
        $this->customerRepository           = $customerRepository;
        $this->_eavConfig                   = $eavConfig;
        $this->companyInterface             = $companyInterface;
        $this->companyRepository            = $companyRepository;
        $this->companyObj                   = $companyObj;
        $this->dataObj                      = $dataObj;
        $this->_helper                      = $helper;
        $this->companyManagement            = $companyManagement;
        $this->companyCreditRepository      = $companyCreditRepository;
        $this->companyPaymentMethodResource = $companyPaymentMethodResource;
        $this->companyPaymentMethodFactory  = $companyPaymentMethodFactory;

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        //echo $customer->getId().'<br />';

        $attribute = $this->_eavConfig->getAttribute('customer', 'application_status');
        $options = $attribute->getSource()->getAllOptions();
        $application_statuses = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $application_status[$option['value']] = $option['label'];
            }
        }

        $attribute = $this->_eavConfig->getAttribute('customer', 'partof_organization');
        $options = $attribute->getSource()->getAllOptions();
        $gpo_list = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $gpo_list[$option['value']] = $option['label'];
            }
        }

        //echo '<pre>'.print_r($gpo_list).'</pre>';

        //$approve_id = array_search("Approved",$application_status);
        //echo $approve_id.'<br />';
        $rejection_comment = '';
        if (!empty($customer->getCustomAttribute("rejection_comment"))) {
            $rejection_comment = $customer->getCustomAttribute("rejection_comment")->getValue();
        }

        $application_status_id = 0;
        if (!empty($customer->getCustomAttribute("application_status"))) {
            $application_status_id = $customer->getCustomAttribute("application_status")->getValue();
        }
        //echo $application_status_id.'<br />';

        //echo $application_status[$application_status_id].'<br />';

        $notify_to_customer = 0;
        if (!empty($customer->getCustomAttribute("notify_to_customer"))) {
            $notify_to_customer = $customer->getCustomAttribute("notify_to_customer")->getValue();
        }

        $set_as_company_account = 0;
        if (!empty($customer->getCustomAttribute("is_company_admin"))) {
            $set_as_company_account = $customer->getCustomAttribute("is_company_admin")->getValue();
        }

        //echo $notify_to_customer.'<br />';
        //echo $set_as_company_account.'<br />'; exit();

        if($application_status[$application_status_id] == 'Approved' && $notify_to_customer == 1){

            try{

                $customer_repository = $this->customerRepository->getById($customer->getId());

                $customer_repository->setCustomAttribute('approved_notification_sent', 1);

                $this->customerRepository->save($customer_repository);

                $error = false;

                $this->inlineTranslation->suspend();

                // Send Email to User
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

                //echo $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope);
                //echo $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope);

                $this->bellNotificationHelper->pushToNotification($customer->getId(), $customer->getId(), 'Account Info Updated', 'Your account has been approved');

                // send mobile notification
                $this->pushNotification->sendPushNotification('customer', 'Customer Account Info Updated', 'Your account has been approved', $customer->getId());

                $sender = [
                    'name' => $this->_escaper->escapeHtml($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope)),
                    'email' => $this->_escaper->escapeHtml($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope)),
                ];
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($sender);

                $templateVars = [];

                $templateVars = [
                    'customer_name' => $customer->getFirstName(),
                ];

                $transport =
                    $this->_transportBuilder
                    ->setTemplateIdentifier('8') // Send the ID of Email template which is created in Admin panel
                    ->setTemplateOptions(
                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                    )
                    ->setTemplateVars($templateVars)
                    ->setFrom($sender)
                    ->addTo($customer->getEmail())
                    //->addCc('maideen.i@gmail.com')
                    ->getTransport();
                $transport->sendMessage();

                $this->inlineTranslation->resume();

                // Send Email to Customer for Promotional Subscription
                $transport =
                    $this->_transportBuilder
                    ->setTemplateIdentifier('86') // Send the ID of Email template which is created in Admin panel
                    ->setTemplateOptions(
                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                    )
                    ->setTemplateVars($templateVars)
                    ->setFrom($sender)
                    ->addTo($customer->getEmail())
                    //->addCc('maideen.i@gmail.com')
                    ->getTransport();
                $transport->sendMessage();

                $this->inlineTranslation->resume();

                // Send Email to Admin
                $this->inlineTranslation->suspend();
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

                //echo $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope);
                //echo $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope);

                $sender = [
                    'name' => $this->_escaper->escapeHtml($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope)),
                    'email' => $this->_escaper->escapeHtml($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope)),
                ];
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($sender);

                $templateVars = [];

                $templateVars = [
                    'customer_name' => $customer->getFirstName(),
                    'customer_email' => $customer->getEmail(),
                ];

                $to_emails = explode(',', $this->_helper->getToEmails());

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
                    //->addCc('maideen.i@gmail.com')
                    ->getTransport();
                $transport->sendMessage();

                $this->inlineTranslation->resume();
            } catch (\Exception $e) {
                \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug($e->getMessage());
                //echo $e->getMessage();
                //exit();
            }

        } else if($application_status[$application_status_id] == 'Rejected' && $notify_to_customer == 1){

            try{

                $customer_repository = $this->customerRepository->getById($customer->getId());

                $customer_repository->setCustomAttribute('rejection_notification_sent', 1);

                $this->customerRepository->save($customer_repository);

                $error = false;

                $this->inlineTranslation->suspend();

                // Send Email to User
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

                //echo $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope);
                //echo $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope);

                $sender = [
                    'name' => $this->_escaper->escapeHtml($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope)),
                    'email' => $this->_escaper->escapeHtml($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope)),
                ];
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($sender);

                $templateVars = [
                    'customer_name' => $customer->getFirstName(),
                    'rejection_comment' => $rejection_comment,
                ];

                $transport =
                    $this->_transportBuilder
                    ->setTemplateIdentifier('7') // Send the ID of Email template which is created in Admin panel
                    ->setTemplateOptions(
                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                    )
                    ->setTemplateVars($templateVars)
                    ->setFrom($sender)
                    ->addTo($customer->getEmail())
                    //->addCc('maideen.i@gmail.com')
                    ->getTransport();
                $transport->sendMessage();


                $this->inlineTranslation->resume();
            } catch (\Exception $e) {
                \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug($e->getMessage());
                //echo $e->getMessage();
                //exit();
            }

        }

        //exit();


        if($application_status[$application_status_id] == 'Approved' && $set_as_company_account == 1) {

            //echo 'set_as_company_account: '.$set_as_company_account.'<br />'; exit();

            $customerId=$customer->getId();
            //Checking whether the customer is associated with any company or not
            $company_id =0;
            $companyData   = $this->companyManagement->getByCustomerId($customerId);
            if($companyData)
                $company_id=$companyData->getId();

            //If Company ID Does not exists then a company will be added and will be associated with the customer
            if(!$company_id) {

                $organization_name = [];
                $legal_business_name = '';
                $company_email = '';
                $company_street = [];
                $company_city = '';
                $company_state = '';
                $company_country = '';
                $company_zip = '';
                $company_phone = '';

                if (!empty($customer->getCustomAttribute("organization_name"))) {
                    $organization_name = $customer->getCustomAttribute("organization_name")->getValue();
                }

                if (!empty($customer->getCustomAttribute("legal_business_name"))) {
                    $legal_business_name = $customer->getCustomAttribute("legal_business_name")->getValue();
                }

                if (!empty($customer->getCustomAttribute("company_email_registered"))) {
                    $company_email = $customer->getCustomAttribute("company_email_registered")->getValue()?$customer->getCustomAttribute("company_email_registered")->getValue():$customer->getEmail();
                }

                if (!empty($customer->getCustomAttribute("company_street"))) {
                    $company_street[] = $customer->getCustomAttribute("company_street")->getValue();
                }

                if (!empty($customer->getCustomAttribute("company_city"))) {
                    $company_city = $customer->getCustomAttribute("company_city")->getValue();
                }

                if (!empty($customer->getCustomAttribute("company_state"))) {
                    $company_state = $customer->getCustomAttribute("company_state")->getValue();
                }

                if (!empty($customer->getCustomAttribute("company_country"))) {
                    $company_country = $customer->getCustomAttribute("company_country")->getValue();
                }

                if (!empty($customer->getCustomAttribute("company_zip"))) {
                    $company_zip = $customer->getCustomAttribute("company_zip")->getValue();
                }

                if (!empty($customer->getCustomAttribute("company_phone"))) {
                    $company_phone = $customer->getCustomAttribute("company_phone")->getValue();
                }

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $write = $resource->getConnection();


                if(!empty($organization_name)) {

                    $companyObj = $objectManager->create('Magento\Company\Model\Company');
                    $companyObj->setCompanyName($organization_name);
                    $companyObj->setCompanyEmail($company_email);
                    $companyObj->setStreet($company_street);
                    $companyObj->setCity($company_city);
                    $companyObj->setCountryId($company_country);
                    $companyObj->setRegion($company_state);
                    $companyObj->setPostcode($company_zip);
                    $companyObj->setTelephone($company_phone);
                    $companyObj->setSuperUserId($customer->getId());
                    $companyObj->setSalesRepresentativeId(1); // give default admin user ID here 
                    $companyObj->setCustomerGroupId(2);
                    $companyObj->setStatus(1);
                    $companyObj->save();

                    $companyId  = $companyObj->getId();

                    // company_advanced_customer_entity
                    /*$insert_CACE_query = "UPDATE company_advanced_customer_entity SET company_id = ".(int)$companyId." WHERE customer_id = ".$customer->getId();
                    $write->query($insert_CACE_query);*/

                    if($companyId && $customerId) {

                        $company_customer = $objectManager->create('Magento\Company\Model\Customer');
                        $company_customer->setCustomerId($customerId);
                        $company_customer->setCompanyId($companyId);
                        $company_customer->save();
                    }

                    // company_credit
                   /* $insert_CC_query = "INSERT INTO company_credit(company_id,balance,currency_code,exceed_limit) VALUES (".$companyId.",'25000.0000','USD','0')";
                    $write->query($insert_CC_query);*/

                    $company_credit = $objectManager->create('Magento\CompanyCredit\Model\CreditLimit');
                    $company_credit->setCompanyId($companyId);
                    $company_credit->setCreditLimit('0');
                    $company_credit->setCurrencyCode('USD');
                    $company_credit->setExceedLimit('0');
                    //$company_credit->setData('balance', '25000.0000');
                    $company_credit->setData('balance', '0.0000');
                    $company_credit->save();

                    //company_payment
                    /*$insert_CP_query = "INSERT INTO company_payment(company_id,applicable_payment_method,available_payment_methods,use_config_settings) VALUES (".$companyId.",'0','purchaseorder','1')";
                    $write->query($insert_CP_query);*/

                    $paymentSettings = $this->companyPaymentMethodFactory->create();
                    $paymentSettings->setCompanyId($companyId);
                    $paymentSettings->setApplicablePaymentMethod(0);
                    $paymentSettings->setAvailablePaymentMethods('purchaseorder');
                    $paymentSettings->setUseConfigSettings(1);
                    $this->companyPaymentMethodResource->save($paymentSettings);

                    //company_structure
                    //$insert_CS_query = "INSERT INTO company_structure(parent_id,entity_id,entity_type,`path`,position,level) VALUES ('0','".$companyId."','0','0','0','0')";
                    /*$insert_CS_query = "INSERT INTO company_structure(parent_id,entity_id,entity_type,`path`,position,level) VALUES ('0','".$customerId."','0','0','0','0')";
                    $write->query($insert_CS_query);
                    $cs_id = $write->lastInsertId();
                    $update_CS_query = "UPDATE company_structure set `path` = '".$cs_id."' where structure_id = ".$cs_id;
                    $write->query($update_CS_query);*/

                    $structure = $objectManager->get('Magento\Company\Model\Company\Structure');
                    $structure->addNode($customerId, '0', '0');

                    //company_roles
                    /*$insert_CR_query = "INSERT INTO company_roles(sort_order,role_name,company_id) VALUES ('0','Default User',".$companyId.")";
                    $write->query($insert_CR_query);
                    $role_id = $write->lastInsertId();*/

                    $role = $objectManager->get('Magento\Company\Model\Role');
                    $role->setCompanyId($companyId);
                    $role->setRoleName('Default User');
                    $role->save();
                    $role_id=$role->getId();

                    //company_permissions
                    $permission_array   = [
                        'Magento_Company::index' =>  \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_Sales::all' => \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_Sales::place_order' => \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_Sales::payment_account' => \Magento\Company\Api\Data\PermissionInterface::DENY_PERMISSION,
                        'Magento_Sales::view_orders' => \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_Sales::view_orders_sub' => \Magento\Company\Api\Data\PermissionInterface::DENY_PERMISSION,
                        'Magento_NegotiableQuote::all' => \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_NegotiableQuote::view_quotes' =>   \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_NegotiableQuote::manage' => \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_NegotiableQuote::checkout' => \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_NegotiableQuote::view_quotes_sub' => \Magento\Company\Api\Data\PermissionInterface::DENY_PERMISSION,
                        'Magento_Company::view' =>\Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_Company::view_account' => \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_Company::edit_account' =>    \Magento\Company\Api\Data\PermissionInterface::DENY_PERMISSION,
                        'Magento_Company::view_address' => \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_Company::edit_address' =>\Magento\Company\Api\Data\PermissionInterface::DENY_PERMISSION,
                        'Magento_Company::contacts' => \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_Company::payment_information' => \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_Company::user_management' => \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_Company::roles_view' => \Magento\Company\Api\Data\PermissionInterface::DENY_PERMISSION,
                        'Magento_Company::roles_edit' => \Magento\Company\Api\Data\PermissionInterface::DENY_PERMISSION,
                        'Magento_Company::users_view' => \Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION,
                        'Magento_Company::users_edit' => \Magento\Company\Api\Data\PermissionInterface::DENY_PERMISSION,
                        'Magento_Company::credit' => \Magento\Company\Api\Data\PermissionInterface::DENY_PERMISSION,
                        'Magento_Company::credit_history' => \Magento\Company\Api\Data\PermissionInterface::DENY_PERMISSION
                    ];

                    $permission = $objectManager->get('Magento\Company\Model\Permission');
                    foreach ($permission_array as $perm_resource => $permission_val) {
                        $permission->setRoleId($role_id);
                        $permission->setResourceId($perm_resource);
                        $permission->setPermission($permission_val);
                        $permission->setId(null);
                        $permission->save();
                    }

                   /* foreach ($permission_array as $perm_resource => $permission_val) {
                        $insert_perm_query = "INSERT INTO company_permissions(role_id,resource_id,permission) VALUES (".$role_id.",'".$perm_resource."','".$permission_val."')";
                        $write->query($insert_perm_query);
                    }*/
                }
            }
        }
        //echo "if this string shows it means observer works";
        //exit();
    }
}
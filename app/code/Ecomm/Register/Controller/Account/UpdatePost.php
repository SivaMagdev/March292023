<?php

namespace Ecomm\Register\Controller\Account;

class UpdatePost extends \Magento\Customer\Controller\Account\EditPost
{
	/**
     * @var \Magento\Customer\Model\EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var Mapper
     */
    private $customerMapper;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * Change customer email or password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */

    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    private function getAuthentication()
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        if (!($this->authentication instanceof AuthenticationInterface)) {
            return $objectManager->get(
                \Magento\Customer\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }
    public function execute()
    {
        $json = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $resultJsonFactory = $objectManager->get(\Magento\Framework\Controller\Result\JsonFactory::class);
        $addressRepository = $objectManager->get("Magento\Customer\Api\AddressRepositoryInterface");
        $dataAddressFactory = $objectManager->get("Magento\Customer\Api\Data\AddressInterfaceFactory");

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());

        //echo '<pre>'.print_r($this->getRequest()->getPost(), true).'</pre>';

        if ($validFormKey && $this->getRequest()->isPost()) {
            $currentCustomerDataObject = $this->getCustomerDataObject($this->session->getCustomerId());

            //echo '<pre>'.print_r($this->getRequest()->getPost(), true).'</pre>';

            try {
            	$eavAttribute = $objectManager->create("Magento\Eav\Model\Entity\Attribute");

            	$customer = $this->customerRepository->getById($this->session->getCustomerId());

            	$exclude_fields = ['form_key', 'steps_status', 'email', 'change_email', 'current_password', 'change_password', 'password', 'password_confirmation', 'billing', 'shipping'];

		        $customer_data = [];

            	foreach($this->getRequest()->getPost() as $code=>$value){

            		if(!in_array($code, $exclude_fields)){
            			$attribute_data = $eavAttribute->loadByCode('customer', $code);

	            		//echo '<pre>'.print_r($attribute_data->getData(), true).'</pre>';

	            		//echo $attribute_data->getIsSystem().'<br />';

	            		if($attribute_data->getIsSystem()){
	            			$customer->setData($code, $value);
	            		} else {
	            			$customer->setCustomAttribute($code, $value);
	            		}

	            		$customer_data[$code] = $value;

            		}
            	}

                if($customer->getCustomAttribute("steps_status")){
                    if($this->getRequest()->getPost('steps_status') > $customer->getCustomAttribute("steps_status")->getValue() ){
                        $customer->setCustomAttribute('steps_status', $this->getRequest()->getPost('steps_status'));
                    }
                } else {
                    $customer->setCustomAttribute('steps_status', $this->getRequest()->getPost('steps_status'));
                }

                //echo '<pre>'.print_r($customer_data, true).'</pre>';
                $this->customerRepository->save($customer);

                if($this->getRequest()->getPost('steps_status') == 1) {

                    $billing_as_corporate = $this->getRequest()->getPost('billing_as_corporate');

                    //echo '-'.$use_corporate;

                    $billing_address_data = [];

                    if($billing_as_corporate == 1){
                        $billing_address_data['street'] = $this->getRequest()->getPost('company_street');
                        $billing_address_data['city'] = $this->getRequest()->getPost('company_city');
                        $billing_address_data['state'] = $this->getRequest()->getPost('company_state');
                        $billing_address_data['country'] = $this->getRequest()->getPost('company_country');
                        $billing_address_data['zip'] = $this->getRequest()->getPost('company_zip');
                        $billing_address_data['phone'] = $this->getRequest()->getPost('company_phone');
                        $billing_address_data['fax'] = $this->getRequest()->getPost('fax_number');
                    } else {
                        $billing_address_data = $this->getRequest()->getPost('billing');
                    }

                    $shipping_address_data = [];
                    $shipping_address_data = $this->getRequest()->getPost('shipping');

                	//echo '<pre>'.print_r($this->getRequest()->getPost('billing'), true).'</pre>';
                    //echo '<pre>'.print_r($this->getRequest()->getPost('shipping'), true).'</pre>';

                	//exit();

                    //echo '<pre>'.print_r($customer->getAddresses(), true).'</pre>';

                    //echo $customer->getFirstName();
                    //echo $customer->getCustomAttribute("organization_name")->getValue();
                    $customerAddress = [];
                    if ($customer->getAddresses()) {

                        //echo '<pre>'.print_r($billing_address_data, true).'</pre>';
                        $billingAddressId = $customer->getDefaultBilling();
                        $shippingAddressId = $customer->getDefaultShipping();

                        $this->updateAddress($billingAddressId, $billing_address_data, $is_default_billing=1, $is_default_shipping=0);
                        $this->updateAddress($shippingAddressId, $shipping_address_data, $is_default_billing=0, $is_default_shipping=1);

                    } else {
                        $this->createAddress($billing_address_data, $is_default_billing=1, $is_default_shipping=0);
                        $this->createAddress($shipping_address_data, $is_default_billing=0, $is_default_shipping=1);
                    }

                    $other_address_data = [];
                    $other_address_data = $this->getRequest()->getPost('other_address');

                    if($other_address_data) {
                        foreach($other_address_data as $address_data){
                            //echo '<pre>'.print_r($address_data, true).'</pre>';
                            if($address_data['address_id'] > 0){
                                $this->updateAddress($address_data['address_id'], $address_data, $is_default_billing=0, $is_default_shipping=0);
                            } else {
                                $this->createAddress($address_data, $is_default_billing=0, $is_default_shipping=0);
                            }
                        }
                    }
                }


                //echo $customer->getEmail();
                if($this->getRequest()->getPost('steps_status') == 5) {

                    $this->sendEmailConfirmation($this->session->getCustomerId());
                    $this->sendEmailToAdmin($this->session->getCustomerId());

                    $customer = $this->customerRepository->getById($this->session->getCustomerId());

                    $_eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

                    $attribute = $_eavConfig->getAttribute('customer', 'application_status');
                    $options = $attribute->getSource()->getAllOptions();
                    $application_statuses = [];
                    foreach ($options as $option) {
                        if ($option['value'] > 0) {
                            $application_status[$option['value']] = $option['label'];
                        }
                    }

                    $pending_id = array_search("Pending Approval",$application_status);

                    $customer->setCustomAttribute('application_status', $pending_id);

                    $this->customerRepository->save($customer);
                }


                $json['status'] = 1;
                $json['msg'] = __('You saved the account information.');
                //$this->messageManager->addSuccessMessage(__('You saved the account information.'));
                //return $resultRedirect->setPath('customer/account');
            } catch (UserLockedException $e) {
                $message = __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                );
                $json['status'] = 0;
                $json['msg'] = $message;
                $this->session->logout();
                $this->session->start();
                //$this->messageManager->addErrorMessage($message);
                //return $resultRedirect->setPath('customer/account/login');
                $json['redirect'] = $resultRedirect->setPath('customer/account/login');
            } catch (InputException $e) {
                //$this->messageManager->addErrorMessage($this->escaper->escapeHtml($e->getMessage()));
                $json['status'] = 0;
                $json['msg'] = $e->getMessage();
                foreach ($e->getErrors() as $error) {
                    //$this->messageManager->addErrorMessage($this->escaper->escapeHtml($error->getMessage()));
                    $json['msg'] .= $error->getMessage();
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                //$this->messageManager->addErrorMessage($e->getMessage());
                $json['status'] = 0;
                $json['msg'] = $e->getMessage();
            } catch (\Exception $e) {
                //$this->messageManager->addException($e, __('We can\'t save the customer.'));
                $json['status'] = 0;
                $json['msg'] =  __('We can\'t save the customer.').$e->getMessage();
                //$json['msg'] =  __('We can\'t save the customer.');
            }

            //$this->session->setCustomerFormData($this->getRequest()->getPostValue());
        }

        //echo '<pre>'.print_r($json, true).'</pre>';

        $result = $resultJsonFactory->create();

        $result->setData($json);

        return $result;
    }

    private function updateAddress($address_id, $data, $is_default_billing=0, $is_default_shipping=0){

        //echo '<pre>'.print_r($data, true).'</pre>'.'-'.$is_default_billing.'-'.$is_default_shipping;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $addressRepository = $objectManager->get("Magento\Customer\Api\AddressRepositoryInterface");

        $customer = $this->customerRepository->getById($this->session->getCustomerId());

        $address = $addressRepository->getById($address_id);

        $street[] = $data['street'];

        $address->setFirstname($customer->getFirstName());
        $address->setLastname($customer->getLastName());
        $address->setStreet($street);
        $address->setCity($data['city']);
        $address->setRegionId($data['state']);
        $address->setCountryId($data['country']); // if Customer country is USA then need add state / province
        $address->setPostcode($data['zip']);
        $address->setTelephone($data['phone']);
        $address->setFax($data['fax']);
        if($customer->getCustomAttribute("organization_name")){
            $address->setCompany($customer->getCustomAttribute("organization_name")->getValue());
        }

        if(isset($data['state_license_id'])){
            $address->setCustomAttribute('state_license_id', $data['state_license_id']);
        }

        if(isset($data['state_license_expiry'])){
            $address->setCustomAttribute('state_license_expiry', $data['state_license_expiry']);
        }

        if(isset($data['state_license_status'])){
            $address->setCustomAttribute('state_license_status', $data['state_license_status']);
        }

        if(isset($data['state_license_id'])){
            $address->setCustomAttribute('dea_license_id', $data['dea_license_id']);
        }

        if(isset($data['state_license_expiry'])){
            $address->setCustomAttribute('state_license_expiry', $data['state_license_expiry']);
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

        $addressRepository->save($address);

    }

    private function createAddress($data, $is_default_billing=0, $is_default_shipping=0){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $addressRepository = $objectManager->get("Magento\Customer\Api\AddressRepositoryInterface");
        $dataAddressFactory = $objectManager->get("Magento\Customer\Api\Data\AddressInterfaceFactory");

        $customer = $this->customerRepository->getById($this->session->getCustomerId());

        $set_address = $dataAddressFactory->create();

        $set_address->setCustomerId($this->session->getCustomerId());

        $street[] = $data['street'];

        $set_address->setFirstname($customer->getFirstName());
        $set_address->setLastname($customer->getLastName());
        $set_address->setStreet($street);
        $set_address->setCity($data['city']);
        $set_address->setRegionId($data['state']);
        $set_address->setCountryId($data['country']); // if Customer country is USA then need add state / province
        $set_address->setPostcode($data['zip']);
        $set_address->setTelephone($data['phone']);
        $set_address->setFax($data['fax']);
        if($customer->getCustomAttribute("organization_name")){
            $set_address->setCompany($customer->getCustomAttribute("organization_name")->getValue());
        }

        if(isset($data['state_license_id'])){
            $set_address->setCustomAttribute('state_license_id', $data['state_license_id']);
        }

        if(isset($data['state_license_expiry'])){
            $set_address->setCustomAttribute('state_license_expiry', $data['state_license_expiry']);
        }

        if(isset($data['state_license_status'])){
            $set_address->setCustomAttribute('state_license_status', $data['state_license_status']);
        }

        if(isset($data['state_license_id'])){
            $set_address->setCustomAttribute('dea_license_id', $data['dea_license_id']);
        }

        if(isset($data['state_license_expiry'])){
            $set_address->setCustomAttribute('state_license_expiry', $data['state_license_expiry']);
        }

        if($is_default_billing == 1){
            $set_address->setIsDefaultBilling('1');
        }

        if($is_default_shipping == 1){
            $set_address->setIsDefaultShipping('1');
        }

        $addressRepository->save($set_address);

    }

    private function sendEmailToAdmin($customerId){
        $error = false;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $scopeConfig = $objectManager->create("Magento\Framework\App\Config\ScopeConfigInterface");
        $transportBuilder = $objectManager->create("Magento\Framework\Mail\Template\TransportBuilder");
        $inlineTranslation = $objectManager->create("Magento\Framework\Translate\Inline\StateInterface");
        $_helper = $objectManager->create("Ecomm\Notification\Helper\Data");

        $inlineTranslation->suspend();


        $customer = $this->customerRepository->getById($customerId);

        $_eavConfig = $objectManager->get('\Magento\Eav\Model\Config');
        $attribute = $_eavConfig->getAttribute('customer', 'partof_organization');
        $Gpo_list = [];
        foreach ($attribute->getSource()->getAllOptions() as $option) {
            if ($option['value'] > 0) {
                $Gpo_list[$option['value']] = $option['label'];
            }
        }

        $businesstype = $_eavConfig->getAttribute('customer', 'business_type');
        $business_types = [];
        foreach ($businesstype->getSource()->getAllOptions() as $option) {
            if ($option['value'] > 0) {
                $business_types[$option['value']] = $option['label'];
            }
        }

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $sender = [
            'name' => $scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];

        //$postObject = new \Magento\Framework\DataObject();
        //$postObject->setData($sender);
        $legal_business_name= "";
        $dba = "";
        $duns_number = "";
        $company_website = "";
        $company_country = "";
        $company_state = "";
        $company_zip = "";
        $company_phone = "";
        $company_fax = "";
        $company_contactperson = "";
        $corporate_contact_name = "";
        $corporate_contact_phone = "";
        $corporate_contact_email = "";
        $purchasing_contact_name = "";
        $purchasing_contact_phone = "";
        $purchasing_contact_email = "";
        $ap_contact_name = "";
        $ap_contact_phone = "";
        $ap_contact_email = "";
        $edi_contact_name = "";
        $edi_contact_phone = "";
        $edi_contact_email = "";
        $ship_contact_name = "";
        $ship_contact_phone = "";
        $ship_contact_email = "";
        $business_type = "";
        $business_other = "";
        $federal_taxid = "";
        $gln_no = "";
        $monthly_purchase = "";
        $idn_affiliation = "";
        $trade_businessname = "";
        $trade_address = "";
        $trade_city = "";
        $trade_state = "";
        $trade_zip = "";
        $trade_phone = "";
        $trade_email = "";
        $bank_name = "";
        $bank_address = "";
        $bank_city = "";
        $bank_state = "";
        $bank_zip = "";
        $bank_contactname = "";
        $bank_phone = "";
        $bank_fax = "";
        $bank_account = "";
        $bank_email = "";
        $partof_organization = "";
        $disproportionate_hospital = "";

        if (!empty($customer->getCustomAttribute("legal_business_name"))) {
            $legal_business_name = $customer->getCustomAttribute("legal_business_name")->getValue();
         }
        if (!empty($customer->getCustomAttribute("dba"))) {
            $dba = $customer->getCustomAttribute("dba")->getValue();
         }
        if (!empty($customer->getCustomAttribute("duns_number"))) {
            $duns_number = $customer->getCustomAttribute("duns_number")->getValue();
         }
        if (!empty($customer->getCustomAttribute("company_website"))) {
           $company_website = $customer->getCustomAttribute("company_website")->getValue();
        }
        if (!empty($customer->getCustomAttribute("company_country"))) {
            $company_country = $customer->getCustomAttribute("company_country")->getValue();
        }
        if (!empty($customer->getCustomAttribute("company_state"))) {
            $company_state = $customer->getCustomAttribute("company_state")->getValue();
        }
        if (!empty($customer->getCustomAttribute("company_zip"))) {
            $company_zip = $customer->getCustomAttribute("company_zip")->getValue();
        }
        if (!empty($customer->getCustomAttribute("company_phone"))) {
            $company_phone = $customer->getCustomAttribute("company_phone")->getValue();
        }
        if (!empty($customer->getCustomAttribute("fax_number"))) {
            $company_fax = $customer->getCustomAttribute("fax_number")->getValue();
        }
        if (!empty($customer->getCustomAttribute("contact_person"))) {
            $company_contactperson = $customer->getCustomAttribute("contact_person")->getValue();
        }
        if (!empty($customer->getCustomAttribute("corporate_contact_name"))) {
            $corporate_contact_name = $customer->getCustomAttribute("corporate_contact_name")->getValue();
        }
        if (!empty($customer->getCustomAttribute("corporate_contact_phone_number"))) {
            $corporate_contact_phone = $customer->getCustomAttribute("corporate_contact_phone_number")->getValue();
        }
        if (!empty($customer->getCustomAttribute("corporate_contact_email_address"))) {
            $corporate_contact_email = $customer->getCustomAttribute("corporate_contact_email_address")->getValue();
        }
        if (!empty($customer->getCustomAttribute("purchasing_contact_name"))) {
            $purchasing_contact_name = $customer->getCustomAttribute("purchasing_contact_name")->getValue();
        }
        if (!empty($customer->getCustomAttribute("purchasing_contact_phone_number"))) {
            $purchasing_contact_phone = $customer->getCustomAttribute("purchasing_contact_phone_number")->getValue();
        }
        if (!empty($customer->getCustomAttribute("purchasing_contact_email_address"))) {
            $purchasing_contact_email = $customer->getCustomAttribute("purchasing_contact_email_address")->getValue();
        }
        if (!empty($customer->getCustomAttribute("accounts_payable_contact_name"))) {
            $ap_contact_name = $customer->getCustomAttribute("accounts_payable_contact_name")->getValue();
        }
        if (!empty($customer->getCustomAttribute("ap_contact_phone_number"))) {
            $ap_contact_phone = $customer->getCustomAttribute("ap_contact_phone_number")->getValue();
        }
        if (!empty($customer->getCustomAttribute("ap_email_address"))) {
            $ap_contact_email = $customer->getCustomAttribute("ap_email_address")->getValue();
        }
        if (!empty($customer->getCustomAttribute("edi_contact_name"))) {
            $edi_contact_name = $customer->getCustomAttribute("edi_contact_name")->getValue();
        }
        if (!empty($customer->getCustomAttribute("edi_contact_phone"))) {
            $edi_contact_phone = $customer->getCustomAttribute("edi_contact_phone")->getValue();
        }
        if (!empty($customer->getCustomAttribute("edi_contact_email"))) {
            $edi_contact_email = $customer->getCustomAttribute("edi_contact_email")->getValue();
        }
        if (!empty($customer->getCustomAttribute("ship_contact_name"))) {
            $ship_contact_name = $customer->getCustomAttribute("ship_contact_name")->getValue();
        }
        if (!empty($customer->getCustomAttribute("ship_contact_phone"))) {
            $ship_contact_phone = $customer->getCustomAttribute("ship_contact_phone")->getValue();
        }
        if (!empty($customer->getCustomAttribute("ship_contact_email"))) {
            $ship_contact_email = $customer->getCustomAttribute("ship_contact_email")->getValue();
        }
        if (!empty($customer->getCustomAttribute("business_type"))) {
            // $business_type = $customer->getCustomAttribute("business_type")->getValue();
            $business_type = $business_types[$customer->getCustomAttribute("business_type")->getValue()];
        }
        if (!empty($customer->getCustomAttribute("business_other"))) {
            $business_other = $customer->getCustomAttribute("business_other")->getValue();
        }
        if (!empty($customer->getCustomAttribute("federal_taxid"))) {
            $federal_taxid = $customer->getCustomAttribute("federal_taxid")->getValue();
        }
        if (!empty($customer->getCustomAttribute("gln_no"))) {
            $gln_no = $customer->getCustomAttribute("gln_no")->getValue();
        }
        if (!empty($customer->getCustomAttribute("monthly_purchase"))) {
            $monthly_purchase = $customer->getCustomAttribute("monthly_purchase")->getValue();
        }
        if (!empty($customer->getCustomAttribute("idn_affiliation"))) {
            $idn_affiliation = $customer->getCustomAttribute("idn_affiliation")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_businessname"))) {
            $trade_businessname = $customer->getCustomAttribute("trade_businessname")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_address"))) {
            $trade_address = $customer->getCustomAttribute("trade_address")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_city"))) {
            $trade_city = $customer->getCustomAttribute("trade_city")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_state"))) {
            $trade_state = $customer->getCustomAttribute("trade_state")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_zip"))) {
            $trade_zip = $customer->getCustomAttribute("trade_zip")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_phone"))) {
            $trade_phone = $customer->getCustomAttribute("trade_phone")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_email"))) {
            $trade_email = $customer->getCustomAttribute("trade_email")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_name"))) {
            $bank_name = $customer->getCustomAttribute("bank_name")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_address"))) {
            $bank_address = $customer->getCustomAttribute("bank_address")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_city"))) {
            $bank_city = $customer->getCustomAttribute("bank_city")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_state"))) {
            $bank_state = $customer->getCustomAttribute("bank_state")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_zip"))) {
            $bank_zip = $customer->getCustomAttribute("bank_zip")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_contactname"))) {
            $bank_contactname = $customer->getCustomAttribute("bank_contactname")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_phone"))) {
            $bank_phone = $customer->getCustomAttribute("bank_phone")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_fax"))) {
            $bank_fax = $customer->getCustomAttribute("bank_fax")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_account"))) {
            $bank_account = $customer->getCustomAttribute("bank_account")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_email"))) {
            $bank_email = $customer->getCustomAttribute("bank_email")->getValue();
        }
        // if (!empty($customer->getCustomAttribute("partof_organization"))) {
        //     $partof_organization = $customer->getCustomAttribute("partof_organization")->getValue();
        // }
        if (!empty($customer->getCustomAttribute("partof_organization"))) {
            $partof_organization = $Gpo_list[$customer->getCustomAttribute("partof_organization")->getValue()];
        }
        // if (!empty($customer->getCustomAttribute("disproportionate_hospital"))) {
        //     $disproportionate_hospital = $customer->getCustomAttribute("disproportionate_hospital")->getValue();
        // }
        if (!empty($customer->getCustomAttribute("disproportionate_hospital"))) {
            if($customer->getCustomAttribute("disproportionate_hospital")->getValue()== 0){
            $disproportionate_hospital = 'No';
            }else{
            $disproportionate_hospital = 'Yes';
            }
        }
        // $bank_account = $customer->getCustomAttribute("bank_account")->getValue();



        $templateVars = [
            'customer_email' => $customer->getEmail(),
            'legal_business_name' => $legal_business_name,
            'dba' => $dba,
            'duns_number' => $duns_number,
            'company_website' => $company_website,
            'company_country' => $company_country,
            // 'company_county' => $company_county,
            'company_state' => $company_state,
            'company_zip' => $company_zip,
            'company_phone' => $company_phone,
            'fax_number' => $company_fax,
            'contact_person' => $company_contactperson,
            'corporate_contact_name' => $corporate_contact_name,
            'corporate_contact_phone_number' => $corporate_contact_phone,
            'corporate_contact_email_address' => $corporate_contact_email,
            'purchasing_contact_name' => $purchasing_contact_name,
            'purchasing_contact_phone_number' => $purchasing_contact_phone,
            'purchasing_contact_email_address' => $purchasing_contact_email,
            'accounts_payable_contact_name' => $ap_contact_name,
            'ap_contact_phone_number' => $ap_contact_phone,
            'ap_email_address' => $ap_contact_email,
            'edi_contact_name' => $edi_contact_name,
            'edi_contact_phone' => $edi_contact_phone,
            'edi_contact_email' => $edi_contact_email,
            'ship_contact_name' => $ship_contact_name,
            'ship_contact_phone' => $ship_contact_phone,
            'ship_contact_email' => $ship_contact_email,
            'business_type' => $business_type,
            'business_other' => $business_other,
            'federal_taxid' => $federal_taxid,
            'gln_no' => $gln_no,
            'monthly_purchase' => $monthly_purchase,
            'idn_affiliation' => $idn_affiliation,
            'trade_businessname' => $trade_businessname,
            'trade_address' => $trade_address,
            'trade_city' =>  $trade_city,
            'trade_state' => $trade_state,
            'trade_zip' => $trade_zip,
            'trade_phone' => $trade_phone,
            'trade_email' => $trade_email,
            'bank_name' => $bank_name,
            'bank_address' => $bank_address,
            'bank_city' => $bank_city,
            'bank_state' => $bank_state,
            'bank_zip' => $bank_zip,
            'bank_contactname' => $bank_contactname,
            'bank_phone' => $bank_phone,
            'bank_fax' => $bank_fax,
            'bank_account' => $bank_account,
            'bank_email' => $bank_email,
            'partof_organization' => $partof_organization,
            'disproportionate_hospital' => $disproportionate_hospital

        ];

        $to_emails = explode(',', $_helper->getToEmails());

        $transport = $transportBuilder
            ->setTemplateIdentifier('17') // Send the ID of Email template which is created in Admin panel
            ->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
            )
            //->setTemplateVars(['data' => $postObject])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            ->addTo($to_emails)
            ->getTransport();
        $transport->sendMessage();
        $inlineTranslation->resume();
    }

    private function sendEmailConfirmation($customerId){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->create("Magento\Framework\App\Config\ScopeConfigInterface");
        $transportBuilder = $objectManager->create("Magento\Framework\Mail\Template\TransportBuilder");
        $inlineTranslation = $objectManager->create("Magento\Framework\Translate\Inline\StateInterface");

        $customer = $this->customerRepository->getById($customerId);

        $_eavConfig = $objectManager->get('\Magento\Eav\Model\Config');
        $attribute = $_eavConfig->getAttribute('customer', 'partof_organization');
        $Gpo_list = [];
        foreach ($attribute->getSource()->getAllOptions() as $option) {
            if ($option['value'] > 0) {
                $Gpo_list[$option['value']] = $option['label'];
            }
        }

        $businesstype = $_eavConfig->getAttribute('customer', 'business_type');
        $business_types = [];
        foreach ($businesstype->getSource()->getAllOptions() as $option) {
            if ($option['value'] > 0) {
                $business_types[$option['value']] = $option['label'];
            }
        }
        

        // print_r($Gop_list);

        $inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sender = [
            'name' => $scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];

        //$postObject = new \Magento\Framework\DataObject();
        //$postObject->setData($sender);
        $legal_business_name= "";
        $dba = "";
        $duns_number = "";
        $company_website = "";
        $company_country = "";
        $company_state = "";
        $company_zip = "";
        $company_phone = "";
        $company_fax = "";
        $company_contactperson = "";
        $corporate_contact_name = "";
        $corporate_contact_phone = "";
        $corporate_contact_email = "";
        $purchasing_contact_name = "";
        $purchasing_contact_phone = "";
        $purchasing_contact_email = "";
        $ap_contact_name = "";
        $ap_contact_phone = "";
        $ap_contact_email = "";
        $edi_contact_name = "";
        $edi_contact_phone = "";
        $edi_contact_email = "";
        $ship_contact_name = "";
        $ship_contact_phone = "";
        $ship_contact_email = "";
        $business_type = "";
        $business_other = "";
        $federal_taxid = "";
        $gln_no = "";
        $monthly_purchase = "";
        $idn_affiliation = "";
        $trade_businessname = "";
        $trade_address = "";
        $trade_city = "";
        $trade_state = "";
        $trade_zip = "";
        $trade_phone = "";
        $trade_email = "";
        $bank_name = "";
        $bank_address = "";
        $bank_city = "";
        $bank_state = "";
        $bank_zip = "";
        $bank_contactname = "";
        $bank_phone = "";
        $bank_fax = "";
        $bank_account = "";
        $bank_email = "";
        $partof_organization = "";
        $disproportionate_hospital = "";

        if (!empty($customer->getCustomAttribute("legal_business_name"))) {
            $legal_business_name = $customer->getCustomAttribute("legal_business_name")->getValue();
         }
        if (!empty($customer->getCustomAttribute("dba"))) {
            $dba = $customer->getCustomAttribute("dba")->getValue();
         }
        if (!empty($customer->getCustomAttribute("duns_number"))) {
            $duns_number = $customer->getCustomAttribute("duns_number")->getValue();
         }
        if (!empty($customer->getCustomAttribute("company_website"))) {
           $company_website = $customer->getCustomAttribute("company_website")->getValue();
        }
        if (!empty($customer->getCustomAttribute("company_country"))) {
            $company_country = $customer->getCustomAttribute("company_country")->getValue();
        }
        if (!empty($customer->getCustomAttribute("company_state"))) {
            $company_state = $customer->getCustomAttribute("company_state")->getValue();
        }
        if (!empty($customer->getCustomAttribute("company_zip"))) {
            $company_zip = $customer->getCustomAttribute("company_zip")->getValue();
        }
        if (!empty($customer->getCustomAttribute("company_phone"))) {
            $company_phone = $customer->getCustomAttribute("company_phone")->getValue();
        }
        if (!empty($customer->getCustomAttribute("fax_number"))) {
            $company_fax = $customer->getCustomAttribute("fax_number")->getValue();
        }
        if (!empty($customer->getCustomAttribute("contact_person"))) {
            $company_contactperson = $customer->getCustomAttribute("contact_person")->getValue();
        }
        if (!empty($customer->getCustomAttribute("corporate_contact_name"))) {
            $corporate_contact_name = $customer->getCustomAttribute("corporate_contact_name")->getValue();
        }
        if (!empty($customer->getCustomAttribute("corporate_contact_phone_number"))) {
            $corporate_contact_phone = $customer->getCustomAttribute("corporate_contact_phone_number")->getValue();
        }
        if (!empty($customer->getCustomAttribute("corporate_contact_email_address"))) {
            $corporate_contact_email = $customer->getCustomAttribute("corporate_contact_email_address")->getValue();
        }
        if (!empty($customer->getCustomAttribute("purchasing_contact_name"))) {
            $purchasing_contact_name = $customer->getCustomAttribute("purchasing_contact_name")->getValue();
        }
        if (!empty($customer->getCustomAttribute("purchasing_contact_phone_number"))) {
            $purchasing_contact_phone = $customer->getCustomAttribute("purchasing_contact_phone_number")->getValue();
        }
        if (!empty($customer->getCustomAttribute("purchasing_contact_email_address"))) {
            $purchasing_contact_email = $customer->getCustomAttribute("purchasing_contact_email_address")->getValue();
        }
        if (!empty($customer->getCustomAttribute("accounts_payable_contact_name"))) {
            $ap_contact_name = $customer->getCustomAttribute("accounts_payable_contact_name")->getValue();
        }
        if (!empty($customer->getCustomAttribute("ap_contact_phone_number"))) {
            $ap_contact_phone = $customer->getCustomAttribute("ap_contact_phone_number")->getValue();
        }
        if (!empty($customer->getCustomAttribute("ap_email_address"))) {
            $ap_contact_email = $customer->getCustomAttribute("ap_email_address")->getValue();
        }
        if (!empty($customer->getCustomAttribute("edi_contact_name"))) {
            $edi_contact_name = $customer->getCustomAttribute("edi_contact_name")->getValue();
        }
        if (!empty($customer->getCustomAttribute("edi_contact_phone"))) {
            $edi_contact_phone = $customer->getCustomAttribute("edi_contact_phone")->getValue();
        }
        if (!empty($customer->getCustomAttribute("edi_contact_email"))) {
            $edi_contact_email = $customer->getCustomAttribute("edi_contact_email")->getValue();
        }
        if (!empty($customer->getCustomAttribute("ship_contact_name"))) {
            $ship_contact_name = $customer->getCustomAttribute("ship_contact_name")->getValue();
        }
        if (!empty($customer->getCustomAttribute("ship_contact_phone"))) {
            $ship_contact_phone = $customer->getCustomAttribute("ship_contact_phone")->getValue();
        }
        if (!empty($customer->getCustomAttribute("ship_contact_email"))) {
            $ship_contact_email = $customer->getCustomAttribute("ship_contact_email")->getValue();
        }
        if (!empty($customer->getCustomAttribute("business_type"))) {
            $business_type = $business_types[$customer->getCustomAttribute("business_type")->getValue()];
        }
        if (!empty($customer->getCustomAttribute("business_other"))) {
            $business_other = $customer->getCustomAttribute("business_other")->getValue();
        }
        if (!empty($customer->getCustomAttribute("federal_taxid"))) {
            $federal_taxid = $customer->getCustomAttribute("federal_taxid")->getValue();
        }
        if (!empty($customer->getCustomAttribute("gln_no"))) {
            $gln_no = $customer->getCustomAttribute("gln_no")->getValue();
        }
        if (!empty($customer->getCustomAttribute("monthly_purchase"))) {
            $monthly_purchase = $customer->getCustomAttribute("monthly_purchase")->getValue();
        }
        if (!empty($customer->getCustomAttribute("idn_affiliation"))) {
            $idn_affiliation = $customer->getCustomAttribute("idn_affiliation")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_businessname"))) {
            $trade_businessname = $customer->getCustomAttribute("trade_businessname")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_address"))) {
            $trade_address = $customer->getCustomAttribute("trade_address")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_city"))) {
            $trade_city = $customer->getCustomAttribute("trade_city")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_state"))) {
            $trade_state = $customer->getCustomAttribute("trade_state")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_zip"))) {
            $trade_zip = $customer->getCustomAttribute("trade_zip")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_phone"))) {
            $trade_phone = $customer->getCustomAttribute("trade_phone")->getValue();
        }
        if (!empty($customer->getCustomAttribute("trade_email"))) {
            $trade_email = $customer->getCustomAttribute("trade_email")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_name"))) {
            $bank_name = $customer->getCustomAttribute("bank_name")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_address"))) {
            $bank_address = $customer->getCustomAttribute("bank_address")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_city"))) {
            $bank_city = $customer->getCustomAttribute("bank_city")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_state"))) {
            $bank_state = $customer->getCustomAttribute("bank_state")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_zip"))) {
            $bank_zip = $customer->getCustomAttribute("bank_zip")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_contactname"))) {
            $bank_contactname = $customer->getCustomAttribute("bank_contactname")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_phone"))) {
            $bank_phone = $customer->getCustomAttribute("bank_phone")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_fax"))) {
            $bank_fax = $customer->getCustomAttribute("bank_fax")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_account"))) {
            $bank_account = $customer->getCustomAttribute("bank_account")->getValue();
        }
        if (!empty($customer->getCustomAttribute("bank_email"))) {
            $bank_email = $customer->getCustomAttribute("bank_email")->getValue();
        }
        if (!empty($customer->getCustomAttribute("partof_organization"))) {
            $partof_organization = $Gpo_list[$customer->getCustomAttribute("partof_organization")->getValue()];
        }
        if (!empty($customer->getCustomAttribute("disproportionate_hospital"))) {
            if($customer->getCustomAttribute("disproportionate_hospital")->getValue()== 0){
            $disproportionate_hospital = 'No';
            }else{
            $disproportionate_hospital = 'Yes';
            }
        }
        // $bank_account = $customer->getCustomAttribute("bank_account")->getValue();



        $templateVars = [
            'legal_business_name' => $legal_business_name,
            'dba' => $dba,
            'duns_number' => $duns_number,
            'company_website' => $company_website,
            'company_country' => $company_country,
            // 'company_county' => $company_county,
            'company_state' => $company_state,
            'company_zip' => $company_zip,
            'company_phone' => $company_phone,
            'fax_number' => $company_fax,
            'contact_person' => $company_contactperson,
            'corporate_contact_name' => $corporate_contact_name,
            'corporate_contact_phone_number' => $corporate_contact_phone,
            'corporate_contact_email_address' => $corporate_contact_email,
            'purchasing_contact_name' => $purchasing_contact_name,
            'purchasing_contact_phone_number' => $purchasing_contact_phone,
            'purchasing_contact_email_address' => $purchasing_contact_email,
            'accounts_payable_contact_name' => $ap_contact_name,
            'ap_contact_phone_number' => $ap_contact_phone,
            'ap_email_address' => $ap_contact_email,
            'edi_contact_name' => $edi_contact_name,
            'edi_contact_phone' => $edi_contact_phone,
            'edi_contact_email' => $edi_contact_email,
            'ship_contact_name' => $ship_contact_name,
            'ship_contact_phone' => $ship_contact_phone,
            'ship_contact_email' => $ship_contact_email,
            'business_type' => $business_type,
            'business_other' => $business_other,
            'federal_taxid' => $federal_taxid,
            'gln_no' => $gln_no,
            'monthly_purchase' => $monthly_purchase,
            'idn_affiliation' => $idn_affiliation,
            'trade_businessname' => $trade_businessname,
            'trade_address' => $trade_address,
            'trade_city' =>  $trade_city,
            'trade_state' => $trade_state,
            'trade_zip' => $trade_zip,
            'trade_phone' => $trade_phone,
            'trade_email' => $trade_email,
            'bank_name' => $bank_name,
            'bank_address' => $bank_address,
            'bank_city' => $bank_city,
            'bank_state' => $bank_state,
            'bank_zip' => $bank_zip,
            'bank_contactname' => $bank_contactname,
            'bank_phone' => $bank_phone,
            'bank_fax' => $bank_fax,
            'bank_account' => $bank_account,
            'bank_email' => $bank_email,
            'partof_organization' => $partof_organization,
            'disproportionate_hospital' => $disproportionate_hospital

        ];

        $transport = $transportBuilder
            ->setTemplateIdentifier('1') // Send the ID of Email template which is created in Admin panel
            ->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
            )
            //->setTemplateVars(['data' => $postObject])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            ->addTo($customer->getEmail())
            //->addCc('maideen.i@gmail.com')
            ->getTransport();
        $transport->sendMessage();
        $inlineTranslation->resume();
    }

    /**
     * Get customer data object
     *
     * @param int $customerId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function getCustomerDataObject($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * Create Data Transfer Object of customer candidate
     *
     * @param \Magento\Framework\App\RequestInterface $inputData
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function populateNewCustomerDataObject(
        \Magento\Framework\App\RequestInterface $inputData,
        \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
    ) {
        $attributeValues = $this->getCustomerMapper()->toFlatArray($currentCustomerData);
        $customerDto = $this->customerExtractor->extract(
            self::FORM_DATA_EXTRACTOR_CODE,
            $inputData,
            $attributeValues
        );
        $customerDto->setId($currentCustomerData->getId());
        if (!$customerDto->getAddresses()) {
            $customerDto->setAddresses($currentCustomerData->getAddresses());
        }
        if (!$inputData->getParam('change_email')) {
            $customerDto->setEmail($currentCustomerData->getEmail());
        }

        return $customerDto;
    }

    /**
     * Change customer password
     *
     * @param string $email
     * @return boolean
     * @throws InvalidEmailOrPasswordException|InputException
     */
    protected function changeCustomerPassword($email)
    {
        $isPasswordChanged = false;
        if ($this->getRequest()->getParam('change_password')) {
            $currPass = $this->getRequest()->getPost('current_password');
            $newPass = $this->getRequest()->getPost('password');
            $confPass = $this->getRequest()->getPost('password_confirmation');
            if ($newPass != $confPass) {
                throw new InputException(__('Password confirmation doesn\'t match entered password.'));
            }

            $isPasswordChanged = $this->customerAccountManagement->changePassword($email, $currPass, $newPass);
        }

        return $isPasswordChanged;
    }

    /**
     * Process change email request
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerDataObject
     * @return void
     * @throws InvalidEmailOrPasswordException
     * @throws UserLockedException
     */
    private function processChangeEmailRequest(\Magento\Customer\Api\Data\CustomerInterface $currentCustomerDataObject)
    {
        if ($this->getRequest()->getParam('change_email')) {
            // authenticate user for changing email
            try {
                $this->getAuthentication()->authenticate(
                    $currentCustomerDataObject->getId(),
                    $this->getRequest()->getPost('current_password')
                );
            } catch (InvalidEmailOrPasswordException $e) {
                throw new InvalidEmailOrPasswordException(
                    __("The password doesn't match this account. Verify the password and try again.")
                );
            }
        }
    }

    /**
     * Get Customer Mapper instance
     *
     * @return Mapper
     *
     * @deprecated 100.1.3
     */
    private function getCustomerMapper()
    {
        if ($this->customerMapper === null) {
            $this->customerMapper = ObjectManager::getInstance()->get(\Magento\Customer\Model\Customer\Mapper::class);
        }
        return $this->customerMapper;
    }

    /**
     * Disable Customer Address Validation
     *
     * @param CustomerInterface $customer
     * @throws NoSuchEntityException
     */
    private function disableAddressValidation($customer)
    {
        foreach ($customer->getAddresses() as $address) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }
}
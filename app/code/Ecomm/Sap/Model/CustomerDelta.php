<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\CustomerDeltaInterface;

class CustomerDelta implements CustomerDeltaInterface {

    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $_helper;

    public $_request;

    protected $storeManager;

    protected $_groupRepository;

    protected $_customerGroup;

    protected $encryptorInterface;

    protected $customerCollection;

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

    protected $_loggerFactory;

    protected $_logger;

	public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection,
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
        \Magento\Eav\Model\Config $eavConfig,
        \Ecomm\Sap\Model\LoggerModel $loggerFactory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_transportBuilder        = $transportBuilder;
        $this->inlineTranslation        = $inlineTranslation;
        $this->scopeConfig              = $scopeConfig;
        $this->_helper                  = $helper;
        $this->_request         		= $request;
        $this->storeManager             = $storeManager;
        $this->_groupRepository         = $groupRepository;
        $this->_customerGroup           = $customerGroup;
        $this->encryptorInterface       = $encryptorInterface;
        $this->customerCollection       = $customerCollection;
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
        $this->_loggerFactory  			= $loggerFactory;
        $this->_logger          		= $logger;
    }

	/**
	 * {@inheritdoc}
	 */
	public function getCustomerDelta()
	{
		$returnData = [];

        $this->_loggerFactory->createLog('CustomerDeltaReq: '.$this->_request->getContent());

        $this->inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sender = [
            'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];

        $templateVars = [];

        $this->_logger->critical('CustomerDelta', ['data' => $this->_request->getContent()]);

        $request_conetent = str_replace('ns1:','',$this->_request->getContent());

        $requestData = json_decode($request_conetent);

        if($requestData){

            /*try {

                $templateVars = ['response' => json_encode($requestData)];

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                //$to_emails = explode(',', $this->_helper->getToEmails());
                $to_emails[] = 'testuser2.pwc@gmail.com';
                //$to_emails[] = 'mohamed.a.ibrahim@pwc.com';
                $transport =
                    $this->_transportBuilder
                    ->setTemplateIdentifier('19') // Send the ID of Email template which is created in Admin panel
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
                $this->inlineTranslation->resume();
                //echo 'email sent';
            } catch (\Exception $e) {
                \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug($e->getMessage());
            }*/

            if($requestData->MT_Customer->CustomerDetails){
                $regions = $this->_regionFactory->create()->getCollection()->addFieldToFilter('country_id', 'US');
                $region_list = [];
                foreach($regions->getData() as $region){

                    $region_list[$region['code']] = $region['region_id'];

                }

                //echo '<pre>'.print_r($region_list, true).'</pre>'; exit();

                $address_statuses = $this->getOptionList('customer_address', 'address_status');

                //echo '<pre>'.print_r($address_statuses, true).'</pre>'; exit();

                $pending_id = array_search('Pending',$address_statuses);
                //echo '<pre>'.print_r($pending_id, true).'</pre>'; exit();

                if(is_array($requestData->MT_Customer->CustomerDetails)){

                    foreach($requestData->MT_Customer->CustomerDetails as $CustomerDetails){

                        //echo '<pre>'.print_r($CustomerDetails, true).'</pre>'; exit();

                        //echo $CustomerDetails->CustomerNumber.' - ';
                        //$collectionData = $this->customerCollection->addAttributeToSelect('*')->addAttributeToFilter('sap_customer_id',(int)$CustomerDetails->CustomerNumber)->getFirstItem();
                        if((int)$CustomerDetails->SoldToParty > 0){
                            $customerCollection = clone $this->customerCollection->addAttributeToSelect('*');
                            $collectionData = $customerCollection->addAttributeToFilter('sap_customer_id',(int)$CustomerDetails->CustomerNumber)->getFirstItem();

                            //echo '<pre>'.print_r($collectionData->getData(), true).'</pre>'; //exit();

                            //echo $collectionData ->getSelect()

                            $customerData=$collectionData->getData();
                            //echo '<pre>'.print_r($customerData, true).'</pre>'; //exit();
                            if($customerData) {

                                //echo $customerData['entity_id'].' - '.$customerData['email'].'  <br />';

                                //echo 'Customer Found: '.(int)$CustomerDetails->CustomerNumber.' - '.$CustomerDetails->CustomerAccountGroup.'-'.$customerData['email'].'-'.$customerData['website_id'].'<br />';

                                $customer_info = $this->_customerRepository->get($customerData['email'],$customerData['website_id']);

                                //echo '<pre>'.print_r($customerData, true).'</pre>'; exit();
                                //if($CustomerDetails->CustomerAccountGroup == 'Z001'){
                                if((int)$CustomerDetails->SoldToParty == (int)$CustomerDetails->ShipToParty) {

                                    $billing_address_data = [];

                                    $billingAddressId = $customerData['default_billing'];

                                    $postalcodes = explode('-',$CustomerDetails->BillingZipcode);

                                    $legal_business_name = 'NA';
                                    if(isset($customerData['legal_business_name'])){

                                        $legal_business_name = $customerData['legal_business_name'];

                                    }

                                    $billing_address_data['address_status'] = $pending_id;
                                    $billing_address_data['customer_id'] = $customerData['entity_id'];
                                    $billing_address_data['first_name'] = $customerData['firstname'];
                                    $billing_address_data['last_name'] = $customerData['lastname'];
                                    $billing_address_data['organization_name'] = $legal_business_name;
                                    $billing_address_data['street'] = $CustomerDetails->BillingSteetAddress;
                                    $billing_address_data['city'] = $CustomerDetails->BillingCity;
                                    $billing_address_data['state'] = $region_list[$CustomerDetails->BillingState];
                                    //$stateCode = 'WA';
                                    //$billing_address_data['state'] = $region_list[$stateCode];
                                    $billing_address_data['zip'] = $postalcodes[0];
                                    $billing_address_data['country'] = $CustomerDetails->BillingCountry;
                                    //$billing_address_data['phone'] = '1234658790';
                                    if(trim($CustomerDetails->Telephone) != '') {
                                        $billing_address_data['phone'] = $CustomerDetails->Telephone;
                                    } else {
                                        $billing_address_data['phone'] = '0000000000';
                                    }
                                    $billing_address_data['sap_address_code'] = (int)$CustomerDetails->BillToParty;

                                    //echo '<pre>billingAddress: '.$billingAddressId.': '.print_r($billing_address_data, true).'</pre>'; exit();

                                    if($billingAddressId == '' && $billingAddressId == 0){
                                        $this->createAddress($billing_address_data, $is_default_billing=1, $is_default_shipping=0);
                                    } else {
                                        $this->updateAddress($billingAddressId, $billing_address_data, $is_default_billing=1, $is_default_shipping=0);
                                    }

                                    $returnData[] = [
                                        'success'=>true,
                                        'CustomerNumber' => $CustomerDetails->CustomerNumber,
                                        'CustomerAccountGroup' => $CustomerDetails->CustomerAccountGroup,
                                        'BillToParty' => $CustomerDetails->BillToParty,
                                        'SoldToParty' => $CustomerDetails->SoldToParty,
                                        'msg' => 'Billing Address Updated'

                                    ];

                                } else {

                                    $addresses = [];

                                    if ($customer_info->getAddresses()) {
                                        foreach($customer_info->getAddresses() as $caddress){

                                            if($caddress->getId() != $customerData['default_billing']){
                                                //echo '<pre>'.print_r($caddress->getId(), true).'</pre>';
                                                if($caddress->getCustomAttribute('sap_address_code')){

                                                    $addresses[$caddress->getId()] = $caddress->getCustomAttribute('sap_address_code')->getValue();

                                                }
                                            }
                                        }
                                    }

                                    //echo '<pre>'.print_r($addresses, true).'</pre>'; exit();

                                    $shipping_address_data = [];

                                    $legal_business_name = 'NA';
                                    if(isset($customerData['legal_business_name'])){

                                        $legal_business_name = $customerData['legal_business_name'];

                                    }

                                    $postalcodes = explode('-',$CustomerDetails->ShippingZipcode);
                                    $shipping_address_data['address_status'] = $pending_id;
                                    $shipping_address_data['customer_id'] = $customerData['entity_id'];
                                    $shipping_address_data['first_name'] = $customerData['firstname'];
                                    $shipping_address_data['last_name'] = $customerData['lastname'];
                                    $shipping_address_data['organization_name'] = $legal_business_name;
                                    //$shipping_address_data['state_license_id'] = $customers_data[24];
                                    //$shipping_address_data['state_license_expiry'] = $customers_data[25];
                                    $shipping_address_data['street'] = $CustomerDetails->ShippingStreetAddress;
                                    $shipping_address_data['city'] = $CustomerDetails->ShippingCity;
                                    $shipping_address_data['state'] = $region_list[$CustomerDetails->ShippingState];
                                    //$stateCode = 'WA';
                                    //$shipping_address_data['state'] = $region_list[$stateCode];
                                    $shipping_address_data['zip'] = $postalcodes[0];
                                    $shipping_address_data['country'] = $CustomerDetails->ShippingCountry;
                                    if(trim($CustomerDetails->Telephone) != '') {
                                        $shipping_address_data['phone'] = $CustomerDetails->Telephone;
                                    } else {
                                        $shipping_address_data['phone'] = '0000000000';
                                    }
                                    $shipping_address_data['sap_address_code'] = (int)$CustomerDetails->ShipToParty;
                                    $shipping_address_data['dea_license_id'] = $CustomerDetails->DEALicense;
                                    $shipping_address_data['dea_license_expiry'] = $CustomerDetails->DEAValidFrom;
                                    //$shipping_address_data['sap_address_code'] = $CustomerDetails->DEAValidTo;
                                    $shipping_address_data['state_license_id'] = $CustomerDetails->StateLicense;
                                    //$shipping_address_data['sap_address_code'] = $CustomerDetails->SLValidFrom;
                                    $shipping_address_data['state_license_expiry'] = $CustomerDetails->SLValidTo;

                                    $shippingAddressId = 0;
                                    $shippingAddressId = array_search((int)$CustomerDetails->ShipToParty, $addresses);
                                    //echo '<pre>shippingAddress: '.$shippingAddressId.': '.print_r($shipping_address_data, true).'</pre>';

                                    if($shippingAddressId){
                                        $this->updateAddress($shippingAddressId, $shipping_address_data, $is_default_billing=0, $is_default_shipping=1);

                                        $returnData[] = [
                                            'success'=>true,
                                            'CustomerNumber' => $CustomerDetails->CustomerNumber,
                                            'CustomerAccountGroup' => $CustomerDetails->CustomerAccountGroup,
                                            'BillToParty' => $CustomerDetails->BillToParty,
                                            'ShipToParty' => $CustomerDetails->ShipToParty,
                                            'msg' => 'Shipping Address Updated'

                                        ];
                                    } else {
                                        $this->createAddress($shipping_address_data, $is_default_billing=0, $is_default_shipping=0);

                                        $returnData[] = [
                                            'success'=>true,
                                            'CustomerNumber' => $CustomerDetails->CustomerNumber,
                                            'CustomerAccountGroup' => $CustomerDetails->CustomerAccountGroup,
                                            'BillToParty' => $CustomerDetails->BillToParty,
                                            'ShipToParty' => $CustomerDetails->ShipToParty,
                                            'msg' => 'Shipping Address Created'

                                        ];
                                    }

                                }

                                $customerObj = $this->_customerRepository->getById($customer_info->getId());

                                //$customerObj->setCustomAttribute('sap_company_code', $CustomerDetails->CompanyCode);
                                $customerObj->setCustomAttribute('sap_distribution_channel', $CustomerDetails->DistributionChannel);
                                $customerObj->setCustomAttribute('sap_division', $CustomerDetails->Division);
                                $customerObj->setCustomAttribute('sap_search_terms', $CustomerDetails->Sortfield);
                                $customerObj->setCustomAttribute('sap_sales_district', $CustomerDetails->SalesDistrict);
                                $customerObj->setCustomAttribute('sap_incoterm', $CustomerDetails->IncotermsPart1);
                                $customerObj->setCustomAttribute('sap_payment_terms', $CustomerDetails->Termsofpaymentkey);
                                $customerObj->setCustomAttribute('sap_incoterm_destination', $CustomerDetails->IncotermsPart2);
                                $customerObj->setCustomAttribute('sap_sold_to_party', $CustomerDetails->SoldToParty);
                                $customerObj->setCustomAttribute('sap_payer', $CustomerDetails->Payer);

                                $cpostalcodes = explode('-',$CustomerDetails->PostalCode2);

                                $customerObj->setCustomAttribute('company_street', $CustomerDetails->Street2);
                                $customerObj->setCustomAttribute('company_city', $CustomerDetails->City2);
                                //$customerObj->setCustomAttribute('company_state', $CustomerDetails->Payer);
                                $customerObj->setCustomAttribute('company_zip', $cpostalcodes[0]);
                                $customerObj->setCustomAttribute('company_country', $CustomerDetails->CountryKey3);
                                $this->_customerRepository->save($customerObj);

                            } else {
                                // Cutomer not found

                                //echo 'Not Found: '.(int)$CustomerDetails->CustomerNumber.'<br />';

                                $returnData[] = [
                                    'success'=>false,
                                    'CustomerNumber' => $CustomerDetails->CustomerNumber,
                                    'CustomerAccountGroup' => $CustomerDetails->CustomerAccountGroup,
                                    'BillToParty' => $CustomerDetails->BillToParty,
                                    'SoldToParty' => $CustomerDetails->ShipToParty,
                                    'msg' => 'Customer Not Found'

                                ];
                            }
                        }
                    }

                } else {

                }

                return $returnData;

            }

        }

        return $returnData;
	}

    private function updateAddress($address_id, $data, $is_default_billing=0, $is_default_shipping=0){

        //echo '<pre>'.print_r($data, true).'</pre>'.'-'.$address_id.' - '.$is_default_billing.'-'.$is_default_shipping;
        echo $address_id.' - '.$is_default_billing.'-'.$is_default_shipping;

        //echo '<pre>'.print_r($data, true).'</pre>';

        $address = $this->_addressRepository->getById($address_id);

        if(isset($data['street']) && $data['street'] != '') {
            $street[] = $data['street'];

            $address->setStreet($street);
        }

        //$address->setCustomerId($data['customer_id']);

        $address->setFirstname($data['first_name']);
        $address->setLastname($data['last_name']);
        $address->setCity($data['city']);
        $address->setRegionId($data['state']);
        $address->setCountryId($data['country']); // if Customer country is USA then need add state / province
        $address->setPostcode($data['zip']);
        $address->setTelephone($data['phone']);
        //$address->setFax($data['fax']);
        $address->setCompany($data['organization_name']);

        $address->setCustomAttribute('address_status', $data['address_status']);


        if(isset($data['state_license_id'])){
            $address->setCustomAttribute('state_license_id', $data['state_license_id']);

            if(isset($data['state_license_expiry'])){
                $address->setCustomAttribute('state_license_expiry', $data['state_license_expiry']);
            }
        }

        if(isset($data['dea_license_id'])){
            $address->setCustomAttribute('dea_license_id', $data['dea_license_id']);

            if(isset($data['dea_license_expiry'])){
                $address->setCustomAttribute('dea_license_expiry', $data['dea_license_expiry']);
            }
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

        //echo 'create address';

        //echo '<pre>'.print_r($data, true).'</pre>'; exit();

        $set_address = $this->dataAddressFactory->create();

        $set_address->setCustomerId($data['customer_id']);

        if(isset($data['street']) && $data['street'] != '') {
            $street[] = $data['street'];
        } else {
            $street[] = 'Address not available';
        }

        //echo '<pre>'.print_r($street, true).'</pre>'; exit();

        $set_address->setCustomAttribute('address_status', $data['address_status']);

        $set_address->setFirstname($data['first_name']);
        $set_address->setLastname($data['last_name']);
        $set_address->setStreet($street);
        $set_address->setCity($data['city']);
        $set_address->setRegionId($data['state']);
        $set_address->setCountryId($data['country']); // if Customer country is USA then need add state / province
        $set_address->setPostcode($data['zip']);
        if(isset($data['phone'])) {
            $set_address->setTelephone($data['phone']);
        } else {
            $set_address->setTelephone('0000000000');
        }
        //$set_address->setFax($data['fax']);
        $set_address->setCompany($data['organization_name']);


        if(isset($data['state_license_id'])){
            $set_address->setCustomAttribute('state_license_id', $data['state_license_id']);
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

    public function sendErrorNotification($params)
    {
        $this->inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sender = [
            'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];

        $templateVars = [];
        $templateVars = $params;

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $to_emails = explode(',', $this->_helper->getToEmails());
        //$to_emails[] = 'maideen.i@gmail.com';
        //$to_emails[] = 'mohamed.a.ibrahim@pwc.com';
        $transport =
            $this->_transportBuilder
            ->setTemplateIdentifier('28') // Send the ID of Email template which is created in Admin panel
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
        $this->inlineTranslation->resume();
        //echo 'email sent';
    }

    public function sendAdminNotification($params)
    {
        $this->inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sender = [
            'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];

        $templateVars = [];
        $templateVars = $params;

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $to_emails = explode(',', $this->_helper->getToEmails());
        //$to_emails[] = 'maideen.i@gmail.com';
        //$to_emails[] = 'mohamed.a.ibrahim@pwc.com';
        $transport =
            $this->_transportBuilder
            ->setTemplateIdentifier('26') // Send the ID of Email template which is created in Admin panel
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
        $this->inlineTranslation->resume();
        //echo 'email sent';
    }
}
<?php
namespace Ecomm\PriceEngine\Controller\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;
use Magento\Directory\Api\CountryInformationAcquirerInterface;

class Alerts extends \Magento\Framework\App\Action\Action
{
    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $_helper;

    protected $_request;

    protected $storeManager;

    protected $_date;

    protected $customerCollection;

    protected $customerInterface;

    protected $customerFactory;

    protected $_customer;

    protected $_customerRepository;

    protected $_addressRepository;

    protected $dataAddressFactory;

    protected $_addressConfig;

    protected $_attributeFactory;

    protected $_eavAttribute;

    protected $_eavConfig;

    protected $_customerGroup;

    protected $_resultJsonFactory;

    protected $countryInformationAcquirerInterface;

    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $dataAddressFactory,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Layout $layout,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        CountryInformationAcquirerInterface $countryInformationAcquirerInterface
    )

    {

        $this->_transportBuilder        = $transportBuilder;
        $this->inlineTranslation        = $inlineTranslation;
        $this->scopeConfig              = $scopeConfig;
        $this->_helper                  = $helper;
        $this->_request                 = $request;
        $this->storeManager             = $storeManager;
        $this->_date                    = $date;
        $this->_customerGroup           = $customerGroup;
        $this->customerCollection       = $customerCollection;
        $this->customerInterface        = $customerInterface;
        $this->customerFactory          = $customerFactory;
        $this->_customer                = $customers;
        $this->_customerRepository      = $customerRepository;
        $this->_addressRepository       = $addressRepository;
        $this->dataAddressFactory       = $dataAddressFactory;
        $this->_addressConfig           = $addressConfig;
        $this->_attributeFactory        = $attributeFactory;
        $this->_eavAttribute            = $eavAttribute;
        $this->_eavConfig               = $eavConfig;
        $this->_resultJsonFactory       = $resultJsonFactory;
        $this->_pageFactory             = $pageFactory;
        $this->countryInformationAcquirerInterface = $countryInformationAcquirerInterface;
        $this->layout = $layout;
        return parent::__construct($context);
    }

    public function execute()
    {

        $sl_threshold = $this->_helper->getSlThreshold();
        $dea_threshold = $this->_helper->getDeaThreshold();

        $today_date = strtotime($this->_date->date('Y-m-d'));
        $sl_notify_date = strtotime("+".$sl_threshold." day", $today_date);
        $dea_notify_date = strtotime("+".$dea_threshold." day", $today_date);

        $sl_expire_date = date('Y-m-d', $sl_notify_date);

        //echo $sl_expire_date.'<br />';

        //echo 'Approved Status ID:'.$this->getApprovedStatusId().'<br />';

        $customerCollection = $this->getFilteredCustomerCollection();

        //echo '<pre>'.print_r($customerCollection->getData(), true).'</pre>'; //exit();

        //echo count($customerCollection);

        if($customerCollection){

            $items = [];

            foreach($customerCollection as $customerObj){

                $customerInfo = $this->_customerRepository->getById($customerObj->getId());

                if ($customerInfo->getAddresses()) {

                    foreach($customerInfo->getAddresses() as $address){

                        //echo '<pre>'.print_r($address->getData(), true).'</pre>';
                        if($address->getCustomAttribute('state_license_expiry')){

                            $state_license_expiry = $address->getCustomAttribute('state_license_expiry')->getValue();

                            $state_license_expiry = strtotime($state_license_expiry);

                            if($state_license_expiry >= $today_date && $state_license_expiry <= $sl_notify_date){
                                //echo date("Y-m-d",$state_license_expiry).'<br />';

                                $state_license_id = '';

                                if($address->getCustomAttribute('state_license_id')){

                                    $state_license_id = $address->getCustomAttribute('state_license_id')->getValue();

                                }

                                //address data
                                $city = $address->getCity();
                                $street = implode( ", ", $address->getStreet());
                                $countryId = $address->getCountryId();
                                $countryName = $this->getCountryName($countryId);
                                $region = $address->getRegion()->getRegion();
                                $postcode = $address->getPostcode();
                                $phone = $address->getTelephone();
                                

                                $data = [
                                    'name' => $customerInfo->getFirstname().' '.$customerInfo->getLastname(),
                                    'email' => $customerInfo->getEmail(),
                                    'licence_id' => $state_license_id,
                                    'licence_expiry' => date("Y-m-d", $state_license_expiry),
                                    'licence_type' => 'State Licence',
                                    'city' => $city,
                                    'street' => $street,
                                    'country' => $countryName,
                                    'region' => $region,
                                    'post_code' => $postcode,
                                    'phone' => $phone
                                ];
                                $items[] = $data;

                                $singleitemsHtml = '';

                                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                                $sender = [
                                    'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                                ];

                                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

                                $transport = $this->_transportBuilder
                                    ->setTemplateIdentifier($this->_helper->emailLicenceExpiryCustomerTemplate())
                                    ->setTemplateOptions(
                                        [
                                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                                        ]
                                    )
                                    ->setTemplateVars(['licence_expiry' => date("Y-m-d", $state_license_expiry),'license_type' => 'State','license_number' => $state_license_id,'city' => $city, 'country' => $countryName, 'region' => $region, 'post_code' => $postcode, 'phone' => $phone, 'street' => $street, 'customer_name' => $customerInfo->getFirstname().' '.$customerInfo->getLastname()])
                                    ->setFrom($sender)
                                    ->addTo($customerInfo->getEmail())
                                    ->getTransport();

                                $transport->sendMessage();
                            }

                        }

                        if($address->getCustomAttribute('dea_license_expiry')){

                            $dea_license_expiry = $address->getCustomAttribute('dea_license_expiry')->getValue();

                            $dea_license_expiry = strtotime($dea_license_expiry);

                            if($dea_license_expiry >= $today_date && $dea_license_expiry <= $dea_notify_date){
                                //echo date("Y-m-d",$dea_license_expiry).'<br />';

                                $dea_license_id = '';

                                if($address->getCustomAttribute('dea_license_id')){

                                    $dea_license_id = $address->getCustomAttribute('dea_license_id')->getValue();

                                }

                                $data = [
                                    'name' => $customerInfo->getFirstname().' '.$customerInfo->getLastname(),
                                    'email' => $customerInfo->getEmail(),
                                    'licence_id' => $dea_license_id,
                                    'licence_expiry' => date("Y-m-d", $dea_license_expiry),
                                    'licence_type' => 'DEA Licence',
                                    'city' => $city,
                                    'street' => $street,
                                    'country' => $countryName,
                                    'region' => $region,
                                    'post_code' => $postcode,
                                    'phone' => $phone
                                ];
                                $items[] = $data;

                                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                                $sender = [
                                    'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                                ];

                                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

                                $transport = $this->_transportBuilder
                                    ->setTemplateIdentifier($this->_helper->emailLicenceExpiryCustomerTemplate())
                                    ->setTemplateOptions(
                                        [
                                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                                        ]
                                    )
                                    ->setTemplateVars(['licence_expiry' => date("Y-m-d", $dea_license_expiry),'license_type' => 'DEA','license_number' => $dea_license_id, 'city' => $city, 'country' => $countryName, 'region' => $region, 'post_code' => $postcode, 'phone' => $phone, 'street' => $street, 'customer_name' => $customerInfo->getFirstname().' '.$customerInfo->getLastname()])
                                    ->setFrom($sender)
                                    ->addTo($customerInfo->getEmail())
                                    ->getTransport();

                                $transport->sendMessage();
                            }

                        }

                    }

                }

            }

            if($items){
                // echo '<pre>'.print_r($items, true).'</pre>';

                $itemsHtml = '';

                $itemsHtml .= '<table class="details" cellpadding="5" cellspacing="2" border="1">';
                    $itemsHtml .= '<tr>';
                        $itemsHtml .= '<th>Customer Name</th>';
                        $itemsHtml .= '<th>Email</th>';
                        $itemsHtml .= '<th>License</th>';
                        $itemsHtml .= '<th>License Type</th>';
                        $itemsHtml .= '<th>Expiry Date</th>';
                        $itemsHtml .= '<th>Address</th>';
                    $itemsHtml .= '</tr>';
                    foreach ($items as $item) {
                        $itemsHtml .= '<tr>';
                            $itemsHtml .= '<td>'.$item['name'].'</td>';
                            $itemsHtml .= '<td>'.$item['email'].'</td>';
                            $itemsHtml .= '<td>'.$item['licence_id'].'</td>';
                            $itemsHtml .= '<td>'.$item['licence_type'].'</td>';
                            $itemsHtml .= '<td>'.$item['licence_expiry'].'</td>';
                            $itemsHtml .= '<td>'.'City: '.$item['city'].'<br/>'.'Street: '.
                            $item['street'].'<br/>'.'Country: '.$item['country'].'<br/>'.'Region: '.
                            $item['region'].'<br/>'.'Post Code: '.$item['post_code'].'<br/>'.
                            'Phone: '.$item['phone'].'</td>';
                        $itemsHtml .= '</tr>';
                    }
                $itemsHtml .= '</table>';

                try {

                    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                    $sender = [
                        'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                        'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                    ];

                    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

                    $to_emails = explode(',', $this->_helper->getToEmails());

                    $transport = $this->_transportBuilder
                        ->setTemplateIdentifier($this->_helper->emailLicenceExpiryTemplate())
                        ->setTemplateOptions(
                            [
                                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                            ]
                        )
                        ->setTemplateVars(['itemsHtml' => $itemsHtml])
                        ->setFrom($sender)
                        ->addTo($to_emails)
                        ->getTransport();

                    $transport->sendMessage();

                    echo 'Licence Expiry Notification sent';
                } catch (\Exception $e) {
                    //echo $e->getMessage();
                    \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug($e->getMessage());
                }
            }
        }


    }

    public function getCustomerCollection() {
        return $this->_customer->getCollection()
               ->addAttributeToSelect("*")
               ->load();
    }

    public function getFilteredCustomerCollection() {
        return $this->customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                //->addAttributeToFilter("is_active", 1)
                ->addAttributeToFilter("application_status", $this->getApprovedStatusId())
                ->load();
    }

    private function getApprovedStatusId(){

        $attribute = $this->_eavConfig->getAttribute('customer', 'application_status');
        $options = $attribute->getSource()->getAllOptions();

        $application_statuses = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $application_statuses[$option['value']] = $option['label'];
            }
        }
        $approved_id = array_search("Approved",$application_statuses);

        return $approved_id;
    }

    /**
     * Getting Country Name
     * @param string $countryCode
     * @param string $type
     *
     * @return null|string
     * */
    public function getCountryName($countryCode, $type="local"){
        $countryName = null;
        try {
            $data = $this->countryInformationAcquirerInterface->getCountryInfo($countryCode);
            if($type == "local"){
                $countryName = $data->getFullNameLocale();
            }else {
                $countryName = $data->getFullNameLocale();
            }
        } catch (NoSuchEntityException $e) {}
        return $countryName;
    }

}
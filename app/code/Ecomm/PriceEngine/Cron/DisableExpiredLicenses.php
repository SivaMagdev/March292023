<?php

namespace Ecomm\PriceEngine\Cron;

use \Psr\Log\LoggerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;

class DisableExpiredLicenses
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

    protected $logger;

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
        LoggerInterface $logger)
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
        $this->layout                   = $layout;
        $this->logger 				    = $logger;
	}


  	public function execute()
    {

        $today_date = $this->_date->date('Y-m-d');

        //echo $today_date.'<br />';

        $today_date = strtotime($today_date);

        //echo 'Customer Approved Status ID:'.$this->getApprovedStatusId().'<br />';
        //echo 'Address Pending Status ID:'.$this->getPendingAddressStatusId().'<br />';

        $address_pending_status_id = $this->getPendingAddressStatusId();

        $customerCollection = $this->getFilteredCustomerCollection();

        //echo '<pre>'.print_r($customerCollection->getData(), true).'</pre>'; //exit();

        //echo count($customerCollection); exit();

        if($customerCollection){

            foreach($customerCollection as $customerObj){

                $customerInfo = $this->_customerRepository->getById($customerObj->getId());

                if ($customerInfo->getAddresses()) {

                    foreach($customerInfo->getAddresses() as $address){

                        //echo '<pre>'.print_r($address->getData(), true).'</pre>';
                        if($address->getCustomAttribute('state_license_expiry')){

                            $state_license_expiry = $address->getCustomAttribute('state_license_expiry')->getValue();

                            $state_license_expiry = strtotime($state_license_expiry);

                            if($state_license_expiry <= $today_date){
                                //echo 'State: '.$address->getId().'<br />';

                                $update_address = $this->_addressRepository->getById($address->getId());
                                $update_address->setCustomAttribute('address_status', $address_pending_status_id);
                                $this->_addressRepository->save($update_address);
                            }

                        }

                        if($address->getCustomAttribute('dea_license_expiry')){

                            $dea_license_expiry = $address->getCustomAttribute('dea_license_expiry')->getValue();

                            $dea_license_expiry = strtotime($dea_license_expiry);

                            if($dea_license_expiry <= $today_date){
                                //echo date("Y-m-d",$dea_license_expiry).'<br />';

                                //echo 'DEA: '.$address->getId().'<br />';

                                $update_address = $this->_addressRepository->getById($address->getId());
                                $update_address->setCustomAttribute('address_status', $address_pending_status_id);
                                $this->_addressRepository->save($update_address);
                            }

                        }

                    }

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

    private function getPendingAddressStatusId(){

        $attribute = $this->_eavConfig->getAttribute('customer_address', 'address_status');
        $options = $attribute->getSource()->getAllOptions();

        $application_statuses = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $application_statuses[$option['value']] = $option['label'];
            }
        }
        $pending_id = array_search("Pending",$application_statuses);

        return $pending_id;
    }
}
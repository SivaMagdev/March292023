<?php 
namespace Ecomm\Restriction\Block;
use Exception;
use Psr\Log\LoggerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Directory\Model\CountryFactory;


class Restriction extends Template
{
    protected $customerSession;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    private $customerRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $countryFactory;

    protected $eavConfig;

    /**
     * @var array
     */
    private $data;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        Context $context,
        LoggerInterface $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        CountryFactory $countryFactory,
         \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->logger = $logger;
          $this->_eavConfig = $eavConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->addressRepository = $addressRepository;
         $this->accountManagement = $accountManagement; 
         $this->customerRepository = $customerRepository;
         $this->countryFactory = $countryFactory;
        parent::__construct($context,$data);
    }

    public function getCustomerAddresses($customerId)
    {
        $addressesList = [];
        try {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                'parent_id',$customerId)->create();
            $addressRepository = $this->addressRepository->getList($searchCriteria);

            foreach($addressRepository->getItems() as $address) {
                 $addressesList[] = $address; 
            }
 
          
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
       }

        return $addressesList;
        
    }

        public function getCustomerlogged()
    {
       return $this->customerSession->getCustomer()->getId();

 

    }

       public function getDefaultShippingId()
    {
         $customerId=$this->customerSession->getCustomer()->getId();
       
        $customer = $this->customerRepository->getById($customerId);
        $shippingAddressId = $customer->getDefaultShipping();
        return $shippingAddressId;

 

    }
           public function getDefaultBillingId()
    {
        $customerId=$this->customerSession->getCustomer()->getId();
        $customer = $this->customerRepository->getById($customerId);
        $billingAddressId = $customer->getDefaultBilling();
        return $billingAddressId;

 

    }
    public function getShippingInfo()
    {
        $customerId=$this->customerSession->getCustomer()->getId();
      
        $customer = $this->customerRepository->getById($customerId);
        $shippingAddressId = $customer->getDefaultShipping();
        // $shippingAddressId = $customer->getDefaultBilling();


        $shippingAddress = $this->addressRepository->getById($shippingAddressId);

        //Get country name
        $countryCode = $shippingAddress->getCountryId();
         $id = $shippingAddress->getId();
        $country = $this->countryFactory->create()->loadByCode($countryCode);
         $country->getName();
         return  $id;
    }

    public function getShippingInfotocart()
    {
        $customerId=$this->customerSession->getCustomer()->getId();
       
        $customer = $this->customerRepository->getById($customerId);
        $shippingAddressId = $customer->getDefaultShipping();
         return  $shippingAddressId;
    }


}
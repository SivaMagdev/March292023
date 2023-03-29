<?php
namespace Ecomm\Sap\Model;

class CustomerMaster
{
    protected $_orderCollectionFactory;

    protected $_orderRepository;

    protected $_groupRepository;

    protected $_customerRepository;

    protected $_addressRepository;

    protected $_addressConfig;

    private $_logger;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_orderCollectionFactory  = $orderCollectionFactory;
        $this->_orderRepository         = $orderRepository;
        $this->_groupRepository         = $groupRepository;
        $this->_customerRepository      = $customerRepository;
        $this->_addressRepository       = $addressRepository;
        $this->_addressConfig           = $addressConfig;
        $this->_logger                  = $logger;
    }

    public function getCustomerData($customer_id) {

        $customerData= $this->_customerRepository->getById($customer_id);
        //echo '<pre>'.print_r($customerData->getId(), true).'</pre>';

        //echo $customerData->getFirstName().'<br />';
        //echo $customerData->getLastName().'<br />';

        $customer_group = $this->_groupRepository->getById($customerData->getGroupId());
        // CORP DEAL DEEX INDV

        $customer_group_code = 'INDV';

        if($customer_group->getCode() == 'General'){
             $customer_group_code = 'INDV';
        } else if($customer_group->getCode() == 'Corporate'){
            $customer_group_code = 'CORP';
        } else if($customer_group->getCode() == 'Dealer'){
            $customer_group_code = 'DEAL';
        } else if($customer_group->getCode() == 'Exscutive Dealer'){
            $customer_group_code = 'DEEX';
        } else if($customer_group->getCode() == 'Employee'){
            $customer_group_code = 'EMPL';
        }

        //exit();

        $sap_customer_id = '';

        $customer_array = array(
            'customer_id'=>$sap_customer_id,
            'customer_firstname'=>$customerData->getFirstName(),
            'customer_lastname'=>$customerData->getLastName(),
            'customer_group'=>$customer_group_code,
            'magento_id'=>$customerData->getId(),
            'customer_email'=>$customerData->getEmail()
        );

        return array('customer'=>$customer_array);


        $sap_customer_id = '';
        $change_indicator = 'I';

        if($_order->getCustomerId() != null){

            $customerData= $this->_customerRepository->getById($_order->getCustomerId());

            $magento_id = $_order->getCustomerId();

            //echo '<pre>'.print_r($customerData, true).'</pre>';

            //echo $customerData->getId().' - '.$customerData->getCustomAttribute('sap_customer_id')->getValue();

            if($customerData->getCustomAttribute('sap_customer_id')){
                $sap_customer_id = $customerData->getCustomAttribute('sap_customer_id')->getValue();
            }
        } else {

            try {
                $websiteId = 1;
                $customer = $this->_customerRepository->get($_order->getCustomerEmail(),$websiteId);
                $magento_id = $customer->getId();
                if($customer->getCustomAttribute('sap_customer_id')){
                    $sap_customer_id = $customer->getCustomAttribute('sap_customer_id')->getValue();
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e){

                $magento_id = '';
                $sap_customer_id = '';

            }

            //echo 'Customer Id: '.$customer->getId();

            //echo 'SAP Customer ID: '.$sap_customer_id;

        }

        if($sap_customer_id > 0){
            $change_indicator = 'U';
        }

        
    }
}
<?php

namespace Ecomm\LoggedCount\Observer;

use Magento\Framework\Event\ObserverInterface;
use Ecomm\LoggedCount\Model\LoggedCountFactory;

class CustomerLogin implements ObserverInterface
{
    protected $_loggedCountFactory;

    public function __construct(
        LoggedCountFactory $loggedCountFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressInterface
    ) {
        $this->_loggedCountFactory = $loggedCountFactory;
        $this->addressInterface = $addressInterface;
         $this->customerRepository = $customerRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
     
     date_default_timezone_set("America/New_York");
        $current_time =  date('Y-m-d H:i:s');
        $region = '';
        $company = '';
        $customer = $observer->getEvent()->getCustomer();
        if (!empty($customer->getDefaultBilling())) { 
            $addid = $customer->getDefaultBilling();
            $billling_address = $this->addressInterface->getById($addid);
            $addresstreet = (implode(" ",$billling_address->getStreet()));
            $region  = $billling_address->getRegion()->getRegion(); 
            if ($billling_address->getCompany() != null) {
                $company = $billling_address->getCompany();
            }
        }

        if ($customer->getCustomAttribute('contact_person') != null) {
            $contact_person = $customer->getCustomAttribute('contact_person')->getValue();
            } 
        else {
            $contact_person = $customer->getContactPerson();
        }
        
        $id = $customer->getId();
        $model = $this->_loggedCountFactory->create()->getCollection()->addFieldToFilter('customer_id', $id);
        $total = 0;
        $firsname=$this->customerRepository->getById($id);
        
        
        if (empty($model->getData())) {
            $total += 1;
            $loogeed = $this->_loggedCountFactory->create();
            $loogeed->setCustomerId($customer->getId());
            $loogeed->setCustomerName($firsname->getFirstName()." ".$firsname->getLastName());
            $loogeed->setCompany($company);
            $loogeed->setBillState($region);
            $loogeed->setSalesRepo($contact_person);
            $loogeed->setCustomerLogin($current_time);
            $loogeed->setYear(date("Y"));
            $loogeed->save();
        } else {
            $data = $model->getData();
            foreach ($data as $key) {
                $idcustomer = $key['id'];
                $customer_login_year = $key['year'];
            }
            $total += 1; 
            $idcustomer;
            $loogeed = $this->_loggedCountFactory->create();
            $loogeed->setCustomerId($id);
             $loogeed->setCustomerName($firsname->getFirstName()." ".$firsname->getLastName());
            $loogeed->setCompany($company);
            $loogeed->setBillState($region);
            $loogeed->setSalesRepo($contact_person);
            $loogeed->setCustomerLogin($current_time);
            $loogeed->setYear(date("Y"));
            $loogeed->save();
        }
    }
}

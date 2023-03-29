<?php

namespace Ecomm\Theme\Plugin\CustomerData;

use Magento\Customer\Helper\Session\CurrentCustomer;

class Customer
{
    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;
    protected $accountManagement;

    public function __construct(
        CurrentCustomer $currentCustomer,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->accountManagement = $accountManagement;
    }

    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $subject, $result)
    {
        if ($this->currentCustomer->getCustomerId()) {
            $customer = $this->currentCustomer->getCustomer();
            $result['company'] = $this->getDefaultShippingAddress($this->currentCustomer->getCustomerId())->getCompany();
        }

        return $result;
    }

    public function getDefaultShippingAddress($customerId)
    {
        try {
            $address = $this->accountManagement->getDefaultShippingAddress($customerId);
        } catch (NoSuchEntityException $e) {
            return __('You have not added default shipping address. Please add default shipping address.');
        }
        return $address;
    }
}
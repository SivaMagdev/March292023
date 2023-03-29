<?php

namespace Ecomm\FirstLogin\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class CustomerLogin implements ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * Constructor
     * @param CustomerRepositoryInterface $customerRepository
     * @param TimezoneInterface $timezone
     * @param DateTime $date
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        TimezoneInterface $timezone,
        DateTime $date
    ) {
        $this->customerRepository = $customerRepository;
        $this->timezone = $timezone;
        $this->date = $date;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        $customerObj = $this->customerRepository->getById($customer->getId());

        $first_login = '';
        if ($customerObj->getCustomAttribute('first_login')) {
        	$first_login = $customerObj->getCustomAttribute('first_login')->getValue();
        }

        if ($first_login == '') {
        	$current_date = $this->timezone->date($this->date->date('Y-m-d H:i:s'))->format('Y-m-d H:i:s');
        	$customerObj->setCustomAttribute('first_login', $current_date);
        	$this->customerRepository->save($customerObj);
        }
    }
}
<?php

declare(strict_types=1);

namespace Ecomm\Api\Model;

use Psr\Log\LoggerInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class SetShippingAddressManagement implements \Ecomm\Api\Api\SetShippingAddressManagementInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        LoggerInterface $logger
    )
    {
        $this->_userContext = $userContext;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerShippingAddress($addressId)
    {
        $customerId = $this->_userContext->getUserId();
        try {
            $address = $this->addressRepository->getById($addressId)->setCustomerId($customerId);
            $address->setIsDefaultShipping(true);

            $this->addressRepository->save($address);
            $response[] = [
                'status' => true, 
                'message' => "This address ID ". "'$addressId'" . " is set as default shipping address" 
            ];
        } catch (\Exception $e) {
            $response[] = [
                'status' => false, 
                'error' => $e->getMessage()
            ];
            // $this->logger->info($e->getMessage());
        }
        return $response;
    }
}


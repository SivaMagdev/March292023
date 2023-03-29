<?php
namespace Ecomm\OrderHin\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Ecomm\OrderHin\Model\HinDataFactory;
 
class SalesOrderHin implements ObserverInterface
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var HinDataFactory
     */
    private $hinFactory;

    public function __construct(
        LoggerInterface $logger,
        AddressRepositoryInterface $addressRepository,
        HinDataFactory $hinFactory
    )
    {
        $this->logger = $logger;
        $this->addressRepository = $addressRepository;
        $this->hinFactory = $hinFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
         $order = $observer->getOrder();
         $this->logger->info(json_encode($order->getData()));
         $orderId = $order->getIncrementId();
         // $this->logger->info("Order Id " . $orderId);

         $shippingAddress = $order->getShippingAddress();
         $addressId = $shippingAddress->getCustomerAddressId();
         $addressInfo = $this->addressRepository->getById($addressId);

         $companyName = $addressInfo->getCompany();
         $organizationName = $order->getData('customer_organization_name');
         $sapAddressId = $addressInfo->getCustomAttribute('sap_address_code')->getValue();
         if ($order->getData('sap_id') > 0) {
            $sapOrderId = $order->getData('sap_id');
         } else {
            $sapOrderId = '';
         }
         // $this->logger->info("SAP Order ID " . $sapOrderId);

         if($addressInfo->getCustomAttribute('hin_id')){
            $hinId = $addressInfo->getCustomAttribute('hin_id')->getValue();
         }else{
            $hinId = null;
         }

         if($addressInfo->getCustomAttribute('hin_status')){
            $hinStatus = $addressInfo->getCustomAttribute('hin_status')->getValue();
         }else{
            $hinStatus = null;
         }

         if($addressInfo->getCustomAttribute('hin_Start')){
            $hinStart = $addressInfo->getCustomAttribute('hin_Start')->getValue();
         }else{
            $hinStart  = null;
         }

         if($addressInfo->getCustomAttribute('hin_end')){
            $hinEnd = $addressInfo->getCustomAttribute('hin_end')->getValue();
         }else{
            $hinEnd = null;
         }

         if($addressInfo->getCustomAttribute('member_id')){
            $memberId = $addressInfo->getCustomAttribute('member_id')->getValue();
         }else{
            $memberId = null;
         }

         if($addressInfo->getCustomAttribute('three_four_b_id')){
            $threeFourtyId = $addressInfo->getCustomAttribute('three_four_b_id')->getValue();
         }else{
            $threeFourtyId = null;
         }

         if($addressInfo->getCustomAttribute('three_four_b_start')){
            $threeFourtyStart = $addressInfo->getCustomAttribute('three_four_b_start')->getValue();
         }else{
            $threeFourtyStart = null;
         }

         if($addressInfo->getCustomAttribute('three_four_b_end')){
            $threeFourtyEnd = $addressInfo->getCustomAttribute('three_four_b_end')->getValue();
         }else{
            $threeFourtyEnd = null;
        }


         $hinData = $this->hinFactory->create();

         // $this->logger->info("line number 59");
         $hinData->setOrderId($orderId);
         $hinData->setHinId($hinId);
         $hinData->setHinStatus($hinStatus);
         $hinData->setHinStart($hinStart);
         $hinData->setHinEnd($hinEnd);
         $hinData->setMemberId($memberId);
         $hinData->setThreeFourBId($threeFourtyId);
         $hinData->setThreeFourBStart($threeFourtyStart);
         $hinData->setThreeFourBEnd($threeFourtyEnd);
         $hinData->setSapOrderId($sapOrderId);
         $hinData->setOrganizationName($organizationName);
         $hinData->setSapAddressId($sapAddressId);
         $hinData->setCompanyName($companyName);
         $hinData->save();
     }
}
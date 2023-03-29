<?php
namespace Ecomm\Theme\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Sales\Model\OrderRepository;


class Order extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerNameGenerationInterface
     */
    private $customerNameGenerator;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CustomerRepositoryInterface $customerRepository,
        CustomerNameGenerationInterface $customerNameGenerator,
        OrderRepository $orderRepository,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerNameGenerator = $customerNameGenerator;
        $this->orderRepository = $orderRepository;
        $this->httpContext = $httpContext;
        parent::__construct($context);
    }

    public function getCreatedBy($order_id)
    {

        $order = $this->orderRepository->get($order_id);

        $customerName = '';
        $customerId = $order->getCustomerId();

        if($order->getShadowLoginName() != ''){
            $customerName = $order->getShadowLoginName();
        } else {
            if ($customerId) {
                $customer = $this->customerRepository->getById($customerId);
                $customerName = $this->customerNameGenerator->getCustomerName($customer);
            }
        }
        return $customerName;
    }
}
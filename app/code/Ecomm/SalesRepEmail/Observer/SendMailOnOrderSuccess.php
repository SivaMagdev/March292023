<?php

namespace Ecomm\SalesRepEmail\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendMailOnOrderSuccess implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderModel;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    protected $dataHelper;

    protected $session;

    protected $orderFactory;
    protected $_customer;
    protected $_customerFactory;
    protected $customerRepository;
    private $paymentHelper;
    /**
     * @param \Magento\Sales\Model\OrderFactory $orderModel
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Checkout\Model\Session $checkoutSession
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Ecomm\SalesRepEmail\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity $identityContainer
    ) {
        
        $this->orderModel = $orderModel;
        $this->orderSender = $orderSender;
        $this->checkoutSession = $checkoutSession;
        $this->dataHelper = $dataHelper;
        $this->session = $session;
        $this->orderFactory = $orderFactory;
        $this->_customerFactory = $customerFactory;
        $this->_customer = $customers;
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->_addressConfig = $addressConfig;
        $this->_order = $order;
        $this->paymentHelper = $paymentHelper;
        $this->identityContainer = $identityContainer;

    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
       
        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getId();
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
        $shippingAddress = $renderer->renderArray($shippingAddress);
        $billingAddress = $renderer->renderArray($billingAddress);
        $customerId = $this->session->getCustomerId();
        $customer = $this->customerRepository->getById($customerId);
        $contact_person_email = '';
        if($customer->getCustomAttribute('contact_person')) {
            $contact_person_email = $customer->getCustomAttribute('contact_person')->getValue();
        }
        if ($contact_person_email != '' && filter_var($contact_person_email, FILTER_VALIDATE_EMAIL)) {
            $transport = [
                'order' => $order,
                'order_id' => $order->getId(),
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($order),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $shippingAddress,
                'formattedBillingAddress' => $billingAddress,
                'created_at_formatted' => $order->getCreatedAtFormatted(2),
                'contact_person_email' => $contact_person_email,
                'order_data' => [
                    'customer_name' => $order->getCustomerName(),
                    'is_not_virtual' => $order->getIsNotVirtual(),
                    'email_customer_note' => $order->getEmailCustomerNote(),
                    'frontend_status_label' => $order->getFrontendStatusLabel()
                ]
            ];
            $this->dataHelper->sendMail($transport);
        }

    }

    private function getPaymentHtml(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        ); 
    }
}

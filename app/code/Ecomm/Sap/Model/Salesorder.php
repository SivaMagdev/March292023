<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\SalesorderInterface;

class SalesOrder implements SalesorderInterface
{
	protected $_dataFactory;

    protected $_orderdataFactory;

    protected $_orderitemdataFactory;

    protected $_orderCollectionFactory;

    protected $_orderRepository;

    protected $_groupRepository;

    protected $_productRepository;

    protected $_customerRepository;

    protected $_addressRepository;

    protected $_addressConfig;

    private $_logger;

	public function __construct(
        \Ecomm\Sap\Api\Data\SalesorderdataInterfaceFactory $dataFactory,
        \Ecomm\Sap\Api\Data\OrderdataInterfaceFactory $orderdataFactory,
        \Ecomm\Sap\Api\Data\OrderItemdataInterfaceFactory $orderitemdataFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_dataFactory             = $dataFactory;
        $this->_orderdataFactory        = $orderdataFactory;
        $this->_orderitemdataFactory    = $orderitemdataFactory;
        $this->_orderCollectionFactory  = $orderCollectionFactory;
        $this->_orderRepository         = $orderRepository;
        $this->_groupRepository         = $groupRepository;
        $this->_productRepository       = $productRepository;
        $this->_customerRepository      = $customerRepository;
        $this->_addressRepository       = $addressRepository;
        $this->_addressConfig           = $addressConfig;
        $this->_logger                  = $logger;
    }

    public function getOrderCollection() {
        $collection = $this->_orderCollectionFactory->create()
         ->addAttributeToSelect('*')
         ->addFieldToFilter($field, $condition); //Add condition if you wish
    }

    public function getFilteredOrderCollection() {

        return $this->_orderCollectionFactory->create()
         ->addFieldToSelect('*')
         ->addFieldToFilter('status', ['in' => 'pending'])
         ->addFieldToFilter('batch_id', ['null' => true])
         ->setOrder('created_at','asc');

    }

	public function getOrderDetails() {

        $page_object    = $this->_dataFactory->create();

        $batch_id = md5(uniqid(rand(), true));

        //echo $batch_id;

        $order = $this->getFilteredOrderCollection();

        $renderer = $this->_addressConfig->getFormatByCode(',')->getRenderer();

        $orders_array = [];

        foreach($order->getData() as $data){

            $order_object = $this->_orderdataFactory->create();

            //echo $data['entity_id'];

            $_order = $this->_orderRepository->get($data['entity_id']);

            //echo '<pre>'.print_r($_order->getData(), true).'</pre>';
            //eco rgdd_delivery_date


            $customerData= $this->_customerRepository->getById($_order->getCustomerId());

            $shippingAddress = $_order->getShippingAddress();
            $billingAddress = $_order->getBillingAddress();

            $sap_customer_id = '';
            $bill_to_party = '';
            $ship_to_party = '';

            if($customerData->getCustomAttribute('sap_customer_id')){
                $sap_customer_id = $customerData->getCustomAttribute('sap_customer_id')->getValue();
            }

            try {

                //echo 'Billing ID:'.$_order->getBillingAddressId();
                //echo 'Shipping ID:'.$_order->getShippingAddressId();
                //echo '<pre>'.print_r($_order->getBillingAddress()->getData(), true).'</pre>';
                //echo '<pre>'.print_r($_order->getBillingAddress()->getCustomerAddressId(), true).'</pre>';
                //echo '<pre>'.print_r($_order->getShippingAddress()->getCustomerAddressId(), true).'</pre>';

                $billing_address_data = $this->_addressRepository->getById($_order->getBillingAddress()->getCustomerAddressId());
                $shipping_address_data = $this->_addressRepository->getById($_order->getShippingAddress()->getCustomerAddressId());

                //echo '<pre>'.print_r($billing_address_data->getData(), true).'</pre>';

                if($billing_address_data->getCustomAttribute('sap_address_code')){
                    $bill_to_party = $billing_address_data->getCustomAttribute('sap_address_code')->getValue();
                }

                if($shipping_address_data->getCustomAttribute('sap_address_code')){
                    $ship_to_party = $shipping_address_data->getCustomAttribute('sap_address_code')->getValue();
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e){

                //echo $e->getMessage();

                $bill_to_party = '';
                $ship_to_party = '';

            }

            $shipping_method = 'standard';

            if($_order->getShippingMethod() == 'expressshipping_expressshipping'){
                $shipping_method = 'express';
            }

            $payment_term = '2/30 Net 31';

            $shipping_waiver = $_order->getShippingDiscountAmount() * -1;
            //$shipping_waiver = $_order->getShippingDiscountAmount();

            $order_object->setOrderId($_order->getId());
            $order_object->setOrderNumber($_order->getIncrementId());
            $order_object->setOrderDate( $_order->getCreatedAt());
            $order_object->setOrderStatus($_order->getStatus());
            $order_object->setOrderTotal($_order->getGrandTotal());
            $order_object->setPaymentTerm($payment_term);
            $order_object->setDeliveryType($shipping_method);
            $order_object->setShippingCost($_order->getShippingAmount());
            $order_object->setShippingWaiver($shipping_waiver);
            $order_object->setCustomerId($_order->getCustomerId());
            $order_object->setSapCustomerId($sap_customer_id);
            $order_object->setBillToParty($bill_to_party);
            $order_object->setShipToParty($ship_to_party);
            $order_object->setBillingAddress($renderer->renderArray($shippingAddress));
            $order_object->setShippingAddress($renderer->renderArray($billingAddress));
            $order_object->setPoNumber($_order->getPayment()->getPoNumber());
            $order_object->setDeliveryDate($_order->getRgddDeliveryDate());
            $order_object->setDeliveryComment($_order->getRgddDeliveryComment());

            $items_array = [];

            foreach ($_order->getAllItems() as $item) {
                //echo '<pre>'.print_r($item->getData(), true).'</pre>';
                if($item->getParentItemId() == NULL) {

                    $attributes = [];
                    $item_options = $item->getProductOptions();

                    $shortdated_batch_id = '';

                    if(isset($item_options['options'])) {

                        //echo '<pre>'.print_r($item_options['options'], true).'</pre>';

                        foreach($item_options['options'] as $options){

                            $shortdated_batch_id = $options['value'];

                        }
                    }
                    if(isset($item_options['attributes_info'])) {
                        foreach($item_options['attributes_info'] as $item_option){
                            //echo '<pre>'.print_r($item_option, true).'</pre>';

                            $attributes[] = $item_option['label'].': '.$item_option['value'];

                        }
                    }

                    $attributes = implode(',', $attributes);

                    $orderitem_object = $this->_orderitemdataFactory->create();

                    //echo $item->getId();
                    $_product = $this->_productRepository->get($item->getSku());


                    $orderitem_object->setItemSku($_product->getMaterial());
                    $orderitem_object->setBatchNumber($shortdated_batch_id);
                    $orderitem_object->setItemName($item->getName());
                    $orderitem_object->setItemQty($item->getQtyOrdered());
                    $orderitem_object->setUnitOfMeasure('SU');
                    $orderitem_object->setItemAmount($item->getPrice());
                    $orderitem_object->setDiscountAmount($item->getDiscountAmount());
                    $row_total = $item->getRowTotalInclTax() - $item->getDiscountAmount();
                    $orderitem_object->setItemNetAmount($row_total);

                    $items_array[] =  $orderitem_object;
                }
            }

            $_order->setBatchId($batch_id);
            $_order->save();

            $order_object->setItem($items_array);

            $orders_array[] =  $order_object;
        }

        if($orders_array) {
            $page_object->setBatchId($batch_id);
            $page_object->setOrders($orders_array);
        }

        return $page_object;

	}
}
<?php
namespace Ecomm\OrderExport\Controller\Customer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Ecomm\PriceEngine\Model\ShortdatedpriceFactory;
use Ecomm\Sap\Model\SalesOrderPodExtension;


class Index extends \Magento\Framework\App\Action\Action
{
    protected $_orderCollectionFactory;
    protected $order;
    protected $fileFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $directoryList;

    private $addressRepository;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    protected $shortdatedpriceFactory;

    protected $salesOrderPodExtensionFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ShortdatedpriceFactory $shortdatedpriceFactory,
        SalesOrderPodExtension $SalesOrderPodExtensionModelFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        AddressRepositoryInterface $addressRepository,
        \Ecomm\Sap\Model\ResourceModel\SalesOrderPodExtension\CollectionFactory $salesOrderPodExtensionFactory,

        \Magento\Customer\Model\Session $customer
    ) {
        $this->_shortdatedpriceFactory = $shortdatedpriceFactory;
        $this->addressFactory = $addressFactory;
        $this->salesOrderPodExtensionFactory = $salesOrderPodExtensionFactory;
        $this->fileFactory = $fileFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->customer = $customer;
        $this->SalesOrderPodExtensionModelFactory = $SalesOrderPodExtensionModelFactory;
        $this->addressRepository = $addressRepository;

        //$this->order = $order;
        parent::__construct($context);
    }
    public function execute()
    {

        $customer = $this->customer;
        $customerName = $customer->getName();
        $customerId = $customer->getId();

        if (!$this->order) {
            $this->order = $this->_orderCollectionFactory
                ->create()
                ->addFieldToSelect("*")
                ->addFieldToFilter("customer_id", $customerId)
                ->setOrder("created_at", "desc");
        }

        $total = $this->order->getData();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $content[] = [
            "po" => __("Customer PO"),
            "entity_id" => __("Order ID"),
            "sku" => __("NDC"),
            "created_at" => __("PO DATE"),
            "carrier" => __("Carrier"),
            "trackingid" => __("Tracking ID"),
            "status" => __("Order status"),
            "delivery_date" => __("Delivery Date"),
            "pharmacy" => __("Pharamcy(Name of Customer)"),
            "address_shipto" => __("Address(ship To)"),
            "state" => __("State license id"),
            "340B" => __("340B"),
            "postcode" => __("Postal Code"),
            "product" => __("Product"),
            "qty_ordered" => __("Quantity Ordered"),
            "qty_fullifilled" => __("Quantity Fulfilled"),
            "qty_unfulfullied" => __("Quantity Unfulfilled"),
            "exp_date_for_special" => __("Exp Date for special"),
            "batch_number_for" => __("Batch Number For"),
            "packprice" => __("Pack Price"),
            "price_type" => __("Price type"),
            "original_total" => __("Original Total"),
            "acutal_total" => __("Actual Total"),
        ];
        $fileName = "order_status.csv"; // Add Your CSV File name
        $filePath =
            $this->directoryList->getPath(DirectoryList::MEDIA) .
            "/" .
            $fileName;
        foreach ($total as $itemdata) {
            $id = $itemdata["entity_id"];
            $order = $objectManager
                ->create("\Magento\Sales\Model\OrderRepository")
                ->get($id);

            if (
                $order->getStatus() === "canceled" ||
                $order->getStatus() == "closed"
            ) {
                $orderItems = $order->getAllItems();
                $order->getEntityId();
                $orderItems = $order->getAllItems();
                /* echo $or = $order->getCompany(); die();*/
                $shippingaddress = $order->getShippingAddress();
                $id = $shippingaddress->getCustomerAddressId();
                $address = $this->addressFactory
                    ->create()
                    ->getCollection()
                    ->addAttributeToSelect("*")
                    ->addFieldToFilter("entity_id", $id)
                    ->getFirstItem();

                if (
                    $myCustomAttribute = $address->getCustomAttribute("hin_id")
                ) {
                    $myCustomAttribute = $myCustomAttribute->getValue();
                } else {
                    $myCustomAttribute = "";
                }
                if (
                    $state_license_id = $address->getCustomAttribute(
                        "state_license_id"
                    )
                ) {
                    $state_license_id = $state_license_id->getValue();
                } else {
                    $state_license_id = "";
                }

                $countrycode = $shippingaddress->getCountryId();
                $company = $shippingaddress->getCompany();
                $region = $shippingaddress->getRegionId();

                $country = $objectManager
                    ->create("\Magento\Directory\Model\Country")
                    ->load("$countrycode")
                    ->getName();
                $region = $objectManager
                    ->create("Magento\Directory\Model\Region")
                    ->load("$region")
                    ->getName();
                $address = implode(" ", $shippingaddress->getStreet());
                $orders = $objectManager
                    ->create("Magento\Sales\Model\Order")
                    ->load($order->getEntityId());

                foreach ($orderItems as $item) {
                    $item_options = $item->getProductOptions();
                    $shortdated_batch_id = "";

                    if (isset($item_options["options"])) {
                        /*        echo '<pre>'.print_r($item_options['options'], true).'</pre>';
                         */
                        foreach ($item_options["options"] as $options) {
                            $shortdated_batch_id = $options["value"];

                            $_shortdated_price_collections = $this->_shortdatedpriceFactory
                                ->create()
                                ->getCollection()
                                ->addFieldToFilter(
                                    "batch",
                                    $shortdated_batch_id
                                )
                                ->addFieldToFilter("ndc", $item->getSku());
                        }
                    }
                    $unfullfiiled = $item->getQtyCanceled();
                    $content[] = [
                        $order->getPayment()->getPoNumber(),
                        $order->getIncrementId(),
                        $item->getSku(),
                        "",
                        "",
                        "",
                        $order->getStatus(),
                        "",
                        //$order->getRgddDeliveryDate(),
                        $company,
                        $address .
                        $shippingaddress->getCity() .
                        $country .
                        $region,
                        $state_license_id,
                        $myCustomAttribute,
                        ($shippingpostcode = $shippingaddress->getPostcode()),
                        $item->getName(),
                        $item->getQtyOrdered(),
                        $item->getQtyOrdered() - $unfullfiiled,
                        $unfullfiiled,
                        "",
                        $shortdated_batch_id,
                        $item->getOriginalPrice(),
                        $item->getPriceType(),
                        $item->getBasePrice() * $item->getQtyOrdered(),
                        $item->getBasePrice() *
                        ($item->getQtyOrdered() - $unfullfiiled),
                    ];
                }
            } else {
                $order->getEntityId();
                $orderItems = $order->getAllItems();
                $shippingaddress = $order->getShippingAddress();
                $id = $shippingaddress->getCustomerAddressId();
                $address = $this->addressFactory
                    ->create()
                    ->getCollection()
                    ->addAttributeToSelect("*")
                    ->addFieldToFilter("entity_id", $id)
                    ->getFirstItem();

                if (
                    $myCustomAttribute = $address->getCustomAttribute("hin_id")
                ) {
                    $myCustomAttribute = $myCustomAttribute->getValue();
                } else {
                    $myCustomAttribute = "";
                }
                if (
                    $state_license_id = $address->getCustomAttribute(
                        "state_license_id"
                    )
                ) {
                    $state_license_id = $state_license_id->getValue();
                } else {
                    $state_license_id = "";
                }

                $company = $shippingaddress->getCompany();
                $countrycode = $shippingaddress->getCountryId();
                $region = $shippingaddress->getRegionId();
                $country = $objectManager
                    ->create("\Magento\Directory\Model\Country")
                    ->load("$countrycode")
                    ->getName();
                $region = $objectManager
                    ->create("Magento\Directory\Model\Region")
                    ->load("$region")
                    ->getName();

                $address = implode(" ", $shippingaddress->getStreet());
                $orders = $objectManager
                    ->create("Magento\Sales\Model\Order")
                    ->load($order->getEntityId());

                $tracksCollection = $orders->getTracksCollection();

                foreach ($orderItems as $item) {
                    $item_options = $item->getProductOptions();
                    $shortdated_batch_id = "";

                    if (isset($item_options["options"])) {
                        foreach ($item_options["options"] as $options) {
                            $shortdated_batch_id = $options["value"];
                        }
                    }
                    if (!empty($shortdated_batch_id)) {
                        $_shortdated_price_collections = $this->_shortdatedpriceFactory
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter("batch", $shortdated_batch_id)
                            ->addFieldToFilter("ndc", $item->getSku())
                            ->getFirstItem();
                        $date = $_shortdated_price_collections->getExpiryDate();
                    } else {
                        $date = "";
                    }
                    $orderid = $order->getSapId();

    

                    $result = $this->SalesOrderPodExtensionModelFactory
                        ->getCollection()
                        ->addFieldToFilter("sales_order_id", $orderid);
                      $result=$result->getData();
                      if (!empty($result)) {
                    foreach ($result as $poddata) {
                        $pod = $poddata["delivery_date"];
                        $pod_status = $poddata["status"];
                        $podtrack = $poddata["track_id"];
                        $podcarrier = $poddata["carrier_code"];
                        $pod_date = $poddata["po_date"];
                    }
                    if (empty($pod)) {
                        $pod = "";
                    }
                    if (empty($pod_status)) {
                        $pod_status = $orders->getStatus();
                    }
                    if (empty($podtrack)) {
                        $podtrack = "";
                    }
                    if (empty($podcarrier)) {
                        $podcarrier = "";
                    }
                    if  (empty($pod_date)) {
                        $pod_date="";
                    }

                    $fullfiiled = $item->getQtyOrdered();
                    $content[] = [
                        $order->getPayment()->getPoNumber(),
                        $order->getIncrementId(),
                        $item->getSku(),
                        $pod_date,
                        $podcarrier,
                        $podtrack,
                        $pod_status,
                        $pod,
                        $company,
                        $address .
                        $shippingaddress->getCity() .
                        $country .
                        $region,

                        $state_license_id,
                        $myCustomAttribute,
                        ($shippingpostcode = $shippingaddress->getPostcode()),
                        $item->getName(),
                        $item->getQtyOrdered(),
                        $fullfiiled,
                        "",
                        $date,
                        $shortdated_batch_id,
                        $item->getBasePrice(),
                        $item->getPriceType(),
                        $item->getBasePrice() * $fullfiiled,
                        $item->getBasePrice() * $item->getQtyOrdered(),
                    ];
                }
                else{    
                    $pod = "";
                  
                        $pod_status = $orders->getStatus();

                        $podtrack = "";
            
                   
                        $podcarrier = "";
                        $pod_date = "";
                
                    $fullfiiled = $item->getQtyOrdered();
                    $content[] = [
                        $order->getPayment()->getPoNumber(),
                        $order->getIncrementId(),
                        $item->getSku(),
                        $pod_date,
                        $podcarrier,
                        $podtrack,
                        $pod_status,
                        $pod,
                        $company,
                        $address .
                        $shippingaddress->getCity() .
                        $country .
                        $region,

                        $state_license_id,
                        $myCustomAttribute,
                        ($shippingpostcode = $shippingaddress->getPostcode()),
                        $item->getName(),
                        $item->getQtyOrdered(),
                        $fullfiiled,
                        "",
                        $date,
                        $shortdated_batch_id,
                        $item->getBasePrice(),
                        $item->getPriceType(),
                        $item->getBasePrice() * $fullfiiled,
                        $item->getBasePrice() * $item->getQtyOrdered(),
                    ];

                }
                }
            }
        }

        $this->csvProcessor
            ->setEnclosure('"')
            ->setDelimiter(",")
            ->saveData($filePath, $content);
        return $this->fileFactory->create(
            $fileName,
            [
                "type" => "filename",
                "value" => $fileName,
                "rm" => true, // True => File will be remove from directory after download.
            ],
            DirectoryList::MEDIA,
            "text/csv",
            null
        );
    }
}

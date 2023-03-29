<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\OrderAsnInterface;
use Ecomm\BellNotification\Helper\BellNotification;
use Ecomm\BellNotification\Helper\PushNotification;
use Ecomm\Sap\Model\AsnExtensionFactory;

class OrderAsn implements OrderAsnInterface {

    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $_helper;

    public $_request;

    protected $_orderFactory;

    protected $_orderRepository;

    protected $_resourceConnection;

    protected $_orderCollectionFactory;

    protected $_productFactory;

    protected $_productModel;

    protected $convertOrder;

    protected $shipmentNotifier;

    protected $trackFactory;

    protected $_loggerFactory;

    protected $bellNotificationHelper;

    protected $pushNotification;

    /**
     * @var AsnExtensionFactory
     */
    protected $asnExtensionFactory;

    protected $_logger;

	public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Ecomm\Sap\Model\LoggerModel $loggerFactory,
        BellNotification $bellNotificationHelper,
        PushNotification $pushNotification,
        AsnExtensionFactory $asnExtensionFactory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_transportBuilder        = $transportBuilder;
        $this->inlineTranslation        = $inlineTranslation;
        $this->scopeConfig              = $scopeConfig;
        $this->_helper                  = $helper;
        $this->_request         		= $request;
        $this->_orderFactory 			= $orderFactory;
        $this->_orderRepository 		= $orderRepository;
        $this->_resourceConnection      = $resourceConnection;
        $this->_orderCollectionFactory  = $orderCollectionFactory;
        $this->_productFactory          = $productFactory;
        $this->_productModel            = $productModel;
        $this->convertOrder             = $convertOrder;
        $this->shipmentNotifier         = $shipmentNotifier;
        $this->trackFactory             = $trackFactory;
        $this->_loggerFactory  			= $loggerFactory;
        $this->bellNotificationHelper   = $bellNotificationHelper;
        $this->pushNotification         = $pushNotification;
        $this->asnExtensionFactory      = $asnExtensionFactory;
        $this->_logger          		= $logger;
    }

	/**
	 * {@inheritdoc}
	 */
	public function updateAsn()
	{
        $this->_loggerFactory->createLog('OrderASNReq: '.$this->_request->getContent());

        $this->inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sender = [
            'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];

        $templateVars = [];

        //$this->_logger->critical('ProductStock', ['data' => $this->_request->getContent()]);

        $requestData = json_decode($this->_request->getContent());
        $IDOCdocumentnumber = 0;

        if($requestData){
        	$connection = $this->_resourceConnection->getConnection();
            foreach($requestData as $orderASN){

            	if($orderASN->IDocReference){

            		$info = $orderASN->IDocReference;
                    $headerinfo = $orderASN->Deliveryheader;
                    $IDOCdocumentnumber = $info->IDOCdocumentnumber;
                    $DeliverDocumentNumber = $headerinfo->DeliverDocumentNumber;
                    $DeliveryItems = $orderASN->DeliveryItem;
                    $DeliveryDeadlines = $orderASN->DeliveryDeadline;
                    $delivery_date = '';
                    foreach($DeliveryDeadlines as $DeliveryDeadline) {
                        if($DeliveryDeadline->DeliveryDate == '006'){
                            $delivery_date = $DeliveryDeadline->Constraintforfinishofactivity;
                        }
                    }
                    $qty_delivered = [];
                    if(is_array($DeliveryItems)){
                        foreach($DeliveryItems as $DeliveryItem){
                            $_product = $this->_productFactory->create()->loadByAttribute('material', (int)$DeliveryItem->MaterialNumber);
                            $qty_delivered[(int)$DeliveryItem->MaterialNumber]['qty_shipped'] = $DeliveryItem->Actualquantitydelivered;
                            $qty_delivered[(int)$DeliveryItem->MaterialNumber]['ndc'] = $_product->getSku();
                        }
                    } else {
                        $_product = $this->_productFactory->create()->loadByAttribute('material', (int)$DeliveryItems->MaterialNumber);
                        $qty_delivered[(int)$DeliveryItems->MaterialNumber]['qty_shipped'] = $DeliveryItems->Actualquantitydelivered;
                        $qty_delivered[(int)$DeliveryItems->MaterialNumber]['ndc'] = $_product->getSku();
                    }

                    //echo '<pre>'.print_r($qty_delivered, true).'</pre>'; exit();

                    if(isset($orderASN->DeliveryTrackingInfo)) {

                        $select = $connection->select()
                        ->from(['esoa' => 'ecomm_sap_order_asn'], ['*'])
                        ->where("esoa.delivery_id = :delivery_id AND esoa.delivery_trigger_status = :delivery_trigger_status");
                        $bind = ['delivery_id'=>$DeliverDocumentNumber, 'delivery_trigger_status'=>1];

                        $data = $connection->fetchRow($select, $bind);

                        if(isset($data['delivery_id']) && (int)$data['delivery_id'] > 0){
                            return $this->getResponseArray(false, $DeliverDocumentNumber, $IDOCdocumentnumber, 'Delivery ID already exist');
                        }

                        $entityInsert = [
                            'sap_id' => $info->IDOCdocumentnumber,
                            'delivery_id' => $headerinfo->DeliverDocumentNumber,
                            'created_at' => date("Y-m-d H:i:s"),
                            'asn_info' => json_encode($requestData),
                        ];

                        $asnExtensionModel = $this->asnExtensionFactory->create();
                        $asnExtensionModel->setData($entityInsert);
                        $asnExtensionModel->save();
                        /*$connection->insert('ecomm_sap_order_asn', $entityInsert);

                        $lastId = $connection->lastInsertId();*/
                        $lastId = $asnExtensionModel->getId();

                        $trackinginfos = $orderASN->DeliveryTrackingInfo;

                        $order_info = $this->_orderCollectionFactory->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('sap_id', ['like' => $IDOCdocumentnumber])
                        ->getFirstItem()
                        ->setOrder('created_at','desc');

                        $_order = $this->_orderRepository->get($order_info['entity_id']);

                        $magento_id = $_order->getId();

                        // Check if order can be shipped or has already shipped
                        if (!$_order->canShip()) {
                            //echo "You can't create an shipment.";
                            $this->_logger->critical('You can\'t create an shipment.', ['data' => $this->_request->getContent()]);
                            return $this->getResponseArray(false, $DeliverDocumentNumber, $IDOCdocumentnumber, 'You can\'t create an shipment.');
                        } else {

                            $shipment = $this->convertOrder->toShipment($_order);

                            // Loop through order items
                            foreach ($_order->getAllItems() AS $orderItem) {
                                // Check if order item has qty to ship or is virtual
                                //if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                                    //continue;
                                //}

                                //$qtyShipped = $orderItem->getQtyToShip();
                                $_productInfo = $this->_productModel->load($orderItem->getProductId());
                                if(isset($qty_delivered[$_productInfo->getMaterial()])){
                                    $qtyShipped = $qty_delivered[$_productInfo->getMaterial()]['qty_shipped'];
                                } else {
                                    $qtyShipped = 0;
                                }

                                //echo 'qtyShipped: '.$qtyShipped; exit();

                                // Create shipment item with qty
                                $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

                                // Add shipment item to shipment
                                $shipment->addItem($shipmentItem);
                            }

                            // Register shipment
                            $shipment->register();

                            //$trackinginfo->TagColumn

                            $shipment->getOrder()->setIsInProcess(true);
                            try {
                                if($trackinginfos){
                                    if(is_array($trackinginfos)) {
                                        $inc = 0;
                                        foreach($trackinginfos as $trackinginfo){

                                            if($inc == 0){
                                                //echo $trackinginfo->TextLine;
                                                if(!isset($trackinginfo->TextLine)){
                                                    $params = [
                                                        'success'=>false,
                                                        'DeliverDocumentNumber' => $DeliverDocumentNumber,
                                                        'IDOCdocumentnumber' => $IDOCdocumentnumber,
                                                        'msg' => 'Tracking Number missing'
                                                    ];

                                                    $this->sendErrorNotification($params);
                                                    return $this->getResponseArray(false, $DeliverDocumentNumber, $IDOCdocumentnumber, 'Tracking Number missing');
                                                } else if(!isset($trackinginfo->CarrierCode)){
                                                    $params = [
                                                        'success'=>false,
                                                        'DeliverDocumentNumber' => $DeliverDocumentNumber,
                                                        'IDOCdocumentnumber' => $IDOCdocumentnumber,
                                                        'msg' => 'Carrier name missing'
                                                    ];
                                                    $this->sendErrorNotification($params);
                                                    return $this->getResponseArray(false, $DeliverDocumentNumber, $IDOCdocumentnumber, 'Carrier name missing');
                                                } else {
                                                    $trackinginfos_array = explode(" ", $trackinginfo->TextLine);
                                                    foreach($trackinginfos_array as $trackingnumber){
                                                        $data = array(
                                                            'carrier_code' => 'custom',
                                                            'title' => $trackinginfo->CarrierCode,
                                                            'number' => $trackingnumber, // Replace with your tracking number
                                                        );
                                                        // Save created shipment and order
                                                        $track = $this->trackFactory->create()->addData($data);
                                                        $shipment->addTrack($track)->save();
                                                    }
                                                }
                                            }
                                            $inc++;
                                        }

                                    } else {
                                        if(!isset($trackinginfos->TextLine)){
                                            $params = [
                                                'success'=>false,
                                                'DeliverDocumentNumber' => $DeliverDocumentNumber,
                                                'IDOCdocumentnumber' => $IDOCdocumentnumber,
                                                'msg' => 'Tracking Number missing'

                                            ];
                                            //echo '<pre>'.print_r($params, true).'</pre>';
                                            $this->sendErrorNotification($params);
                                            return $this->getResponseArray(false, $DeliverDocumentNumber, $IDOCdocumentnumber, 'Tracking Number missing');
                                        } else if(!isset($trackinginfos->CarrierCode)){
                                            $params = [
                                                'success'=>false,
                                                'DeliverDocumentNumber' => $DeliverDocumentNumber,
                                                'IDOCdocumentnumber' => $IDOCdocumentnumber,
                                                'msg' => 'Carrier Name missing'

                                            ];
                                            $this->sendErrorNotification($params);
                                            return $this->getResponseArray(false, $DeliverDocumentNumber, $IDOCdocumentnumber, 'Carrier Name missing');
                                        } else {
                                            $trackinginfos_array = explode(" ", $trackinginfos->TextLine);
                                            foreach($trackinginfos_array as $trackingnumber){
                                                $data = array(
                                                    'carrier_code' => 'custom',
                                                    'title' => $trackinginfos->CarrierCode,
                                                    'number' => $trackingnumber, // Replace with your tracking number
                                                );
                                                // Save created shipment and order
                                                $track = $this->trackFactory->create()->addData($data);
                                                $shipment->addTrack($track)->save();
                                            }
                                        }
                                    }
                                }
                                // Save created shipment and order
                                if($delivery_date!=''){
                                    $comment = 'Expected Delivery Date: '.$delivery_date;
                                    $shipment->addComment($comment, true, true);
                                    $shipment->setCustomerNote($comment);
                                    $shipment->setCustomerNoteNotify(true);
                                }
                                $shipment->save();
                                $shipment->getOrder()->save();

                                // Send email
                                $this->shipmentNotifier->notify($shipment);

                                $shipment->save();

                                $m_delivery_id = $shipment->getId();

                                $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
                                $_order->setState($orderState)->setStatus('out_for_delivery');
                                $eMessage = sprintf("Delivery created with number: %s", $DeliverDocumentNumber);
                                $_order->addCommentToStatusHistory($eMessage);
                                $_order->save();

                                /*$entityUpdate = [
                                    'magento_id' => $magento_id,
                                    'm_delivery_id' => $m_delivery_id,
                                    'rdd_date' => $delivery_date
                                ];

                                $connection->update(
                                    'ecomm_sap_order_asn',
                                    $entityUpdate,
                                    ['id = ?' => (int)$lastId]
                                );*/
                                $entityInsert = [
                                    'id' => $lastId,
                                    'delivery_trigger_status' => 1,
                                    'magento_id' => $magento_id,
                                    'm_delivery_id' => $m_delivery_id,
                                    'rdd_date' => $delivery_date
                                ];

                                //$asnExtensionModel = $this->asnExtensionFactory->create();
                                $asnExtensionModel->setData($entityInsert);
                                $asnExtensionModel->save();
                                $params = [
                                    'customer_name' => 'Admin',
                                    'order_status' => 'Out for Delivery',
                                    'order_number' => $_order->getIncrementId(),
                                    'sap_order_number' => $IDOCdocumentnumber,
                                    'po_number' => $_order->getPayment()->getPoNumber()
                                ];

                                //echo '<pre>'.print_r($params, true).'</pre>';

                                $this->bellNotificationHelper->pushToNotification($_order->getId(),$_order->getCustomerId(),'Sales Order',$_order->getIncrementId().' - Status of your order number has changed to Out for Delivery');

                                $this->pushNotification->sendPushNotification('order', 'Order Status Updated', 'Order #'.$_order->getIncrementId().' - Out for Delivery', $_order->getCustomerId());

                                //$this->sendAdminNotification($params);
                            } catch (\Exception $e) {

                                $this->_loggerFactory->createLog('ASN::ERROR:: '.$e->getMessage());
                                return $this->getResponseArray(false, $DeliverDocumentNumber, $IDOCdocumentnumber, $e->getTraceAsString());
                            }
                        }
                    } else {
                        return $this->getResponseArray(false, $DeliverDocumentNumber, $IDOCdocumentnumber, 'Tracking Header is not found in JSON');
                    }
            	}

            }

            if($IDOCdocumentnumber == 0){
                return $this->getResponseArray(false, $DeliverDocumentNumber, $IDOCdocumentnumber, 'Invalid sales order number');
            } else {
                return $this->getResponseArray(true, $DeliverDocumentNumber, $IDOCdocumentnumber, '');
            }
        }
	}

    private function getResponseArray($status = false, $deliverDocumentNumber = 0, $idocDocumentNumber = 0, $message = '')
    {
        $returnData[] = [
            'success'=>$status,
            'DeliverDocumentNumber'=> $deliverDocumentNumber,
            'IDOCdocumentnumber' => $idocDocumentNumber,
            'msg' => $message

        ];
        return $returnData;
    }

    public function sendErrorNotification($params)
    {
        $this->inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sender = [
            'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];

        $templateVars = [];
        $templateVars = $params;

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $to_emails = explode(',', $this->_helper->getToEmails());
        //$to_emails[] = 'maideen.i@gmail.com';
        //$to_emails[] = 'mohamed.a.ibrahim@pwc.com';
        $transport =
            $this->_transportBuilder
            ->setTemplateIdentifier('28') // Send the ID of Email template which is created in Admin panel
            ->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
            )
            //->setTemplateVars(['data' => $postObject])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            //->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
            ->addTo($to_emails)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
        //echo 'email sent';
    }

    public function sendAdminNotification($params)
    {
        $this->inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sender = [
            'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];

        $templateVars = [];
        $templateVars = $params;

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $to_emails = explode(',', $this->_helper->getToEmails());
        //$to_emails[] = 'maideen.i@gmail.com';
        //$to_emails[] = 'mohamed.a.ibrahim@pwc.com';
        $transport =
            $this->_transportBuilder
            ->setTemplateIdentifier('26') // Send the ID of Email template which is created in Admin panel
            ->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
            )
            //->setTemplateVars(['data' => $postObject])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            //->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
            ->addTo($to_emails)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
        //echo 'email sent';
    }
}
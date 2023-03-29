<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\OrderAckInterface;
use Ecomm\BellNotification\Helper\BellNotification;
use Ecomm\BellNotification\Helper\PushNotification;

class OrderAck implements OrderAckInterface {

    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $helper;

    public $_request;

    protected $_orderFactory;

    protected $_orderInterface;

    protected $_orderRepository;

    protected $_productFactory;

    protected $_productModel;

    protected $_resourceConnection;

    protected $_orderCollectionFactory;

    protected $orderManagement;

    protected $orderHistoryFactory;

    protected $_loggerFactory;

    protected $bellNotificationHelper;

    protected $pushNotification;

    protected $_logger;

	public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory,
        \Ecomm\Sap\Model\LoggerModel $loggerFactory,
        BellNotification $bellNotificationHelper,
        PushNotification $pushNotification,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_transportBuilder        = $transportBuilder;
        $this->inlineTranslation        = $inlineTranslation;
        $this->scopeConfig              = $scopeConfig;
        $this->helper                   = $helper;
        $this->_request         		= $request;
        $this->_orderFactory 			= $orderFactory;
        $this->_orderInterface 		    = $orderInterface;
        $this->_orderRepository         = $orderRepository;
        $this->_productFactory          = $productFactory;
        $this->_productModel            = $productModel;
        $this->_resourceConnection      = $resourceConnection;
        $this->_orderCollectionFactory  = $orderCollectionFactory;
        $this->orderManagement          = $orderManagement;
        $this->orderHistoryFactory      = $orderHistoryFactory;
        $this->_loggerFactory  			= $loggerFactory;
        $this->bellNotificationHelper   = $bellNotificationHelper;
        $this->pushNotification         = $pushNotification;
        $this->_logger          		= $logger;
    }

	/**
	 * {@inheritdoc}
	 */
	public function updateAck()
	{
        $this->_loggerFactory->createLog('OrderAckReq: '.$this->_request->getContent());
        $request_conetent = str_replace('ns0:','',$this->_request->getContent());

        $requestData = json_decode($request_conetent);

        $MagentoID = 0;
        $IDOCdocumentnumber = 0;

        if($requestData){

            $connection = $this->_resourceConnection->getConnection();

            $entityInsert = [
                'created_at' => date("Y-m-d H:i:s"),
                'ack_info' => $this->_request->getContent(),
            ];
            $connection->insert('ecomm_sap_order_ack', $entityInsert);

            $lastId = $connection->lastInsertId();
            try{

                $MagentoID = $requestData->MT_OrderAck->Documentheaderreferencedata->MagentoID;
                $IDOCdocumentnumber = $requestData->MT_OrderAck->Documentheaderreferencedata->IDOCdocumentnumber;

                if($MagentoID == 0 || $IDOCdocumentnumber == 0){
                    return $this->getResponseArray(false, $MagentoID, $IDOCdocumentnumber, 'Magento ID or Idoc number is not valid');
                } else {

                    $entityUpdate = [
                        'order_id' => $MagentoID,
                        'sap_id' => $IDOCdocumentnumber
                    ];

                    $connection->update(
                        'ecomm_sap_order_ack',
                        $entityUpdate,
                        ['id = ?' => (int)$lastId]
                    );

                    $ORDERCancellation = [];

                    $ORDERCancellation = $requestData->MT_OrderAck->ORDERCancellation;
                    if (is_array($ORDERCancellation)) {
                        $ORDERCancellation = array_filter($ORDERCancellation);
                    }

                    if ($ORDERCancellation) {

                        $ack_shipping_cost = 0;
                        if(isset($requestData->MT_OrderAck->DocumentItemGeneralData->ShippingCost)){
                            $ack_shipping_cost = $requestData->MT_OrderAck->DocumentItemGeneralData->ShippingCost;
                        }
                        $qty_cancelled = [];

                        $cancelledReason = [];

                        $cancelComment = '';

                        if(is_array($ORDERCancellation)){
                            foreach($ORDERCancellation as $CancelledItem){
                                $_product = $this->_productFactory->create()->loadByAttribute('material', (int)$CancelledItem->MaterialNumber);
                                $qty_cancelled[(int)$CancelledItem->MaterialNumber]['material'] = (int)$CancelledItem->MaterialNumber;
                                $qty_cancelled[(int)$CancelledItem->MaterialNumber]['qty_cancelled'] = $CancelledItem->Quantity;
                                $qty_cancelled[(int)$CancelledItem->MaterialNumber]['ndc'] = $_product->getSku();

                                $cancelledReason[] = $_product->getSku().': '.$CancelledItem->CancelReason;
                            }
                        } else {
                            $_product = $this->_productFactory->create()->loadByAttribute('material', (int)$ORDERCancellation->MaterialNumber);
                            $qty_cancelled[(int)$ORDERCancellation->MaterialNumber]['material'] = (int)$ORDERCancellation->MaterialNumber;
                            $qty_cancelled[(int)$ORDERCancellation->MaterialNumber]['qty_cancelled'] = $ORDERCancellation->Quantity;
                            $qty_cancelled[(int)$ORDERCancellation->MaterialNumber]['ndc'] = $_product->getSku();

                            $cancelledReason[] = $_product->getSku().': '.$ORDERCancellation->CancelReason;
                        }

                        $cancelComment = implode(', ', $cancelledReason);

                        $order_info = $this->_orderCollectionFactory->create()
                            ->addFieldToSelect('*')
                            ->addFieldToFilter('sap_id', ['like' => $IDOCdocumentnumber])
                            ->getFirstItem()
                            ->setOrder('created_at','desc');

                        $_order = $this->_orderRepository->get($order_info['entity_id']);

                        $orderId = $_order->getId();

                        if ($_order->canCancel()) {

                            $fullCancel = true;

                            $grand_total= 0;
                            $sub_total= 0;

                            foreach ($_order->getAllItems() as $orderItem) {

                                $qtyCancelled = 0;

                                $row_total = 0;

                                $_productInfo = $this->_productModel->load($orderItem->getProductId());
                                if(isset($qty_cancelled[$_productInfo->getMaterial()])){
                                    $qtyCancelled = $qty_cancelled[$_productInfo->getMaterial()]['qty_cancelled'];

                                    $qtyCancelled = $orderItem->getQtyCanceled()+$qtyCancelled;

                                    $current_qty = $orderItem->getQtyOrdered()-$qtyCancelled;

                                    $row_total = $current_qty*$orderItem->getPrice();
                                    if($orderItem->getQtyOrdered() != $qtyCancelled) {
                                        $fullCancel = false;
                                    }
                                    $sub_total += $row_total;
                                } else {
                                    $qtyCancelled = 0;

                                    $fullCancel = false;

                                    $row_total = $orderItem->getRowTotal();

                                    $sub_total += $orderItem->getRowTotal();
                                }
                                if($qtyCancelled >0) {
                                    $orderItem->setRowTotal($row_total);
                                    $orderItem->setBaseRowTotal($row_total);
                                    $orderItem->setRowTotalInclTax($row_total);
                                    $orderItem->setBaseRowTotalInclTax($row_total);
                                    $orderItem->setQtyCanceled($qtyCancelled);
                                    $orderItem->save();
                                }
                            }

                            $_order->setBaseSubTotal($sub_total);
                            $_order->setSubTotal($sub_total);
                            $_order->setBaseSubTotalInclTax($sub_total);
                            $_order->setSubTotalInclTax($sub_total);

                            $_order->setSubTotal($sub_total);
                            $_order->setBaseGrandTotal($sub_total);

                            $shipping_cost = $_order->getShippingAmount();

                            if($shipping_cost == 0 && $ack_shipping_cost > 0) {

                                $_order->setBaseShippingAmount($ack_shipping_cost);
                                $_order->setShippingAmount($ack_shipping_cost);

                                $shipping_cost = $ack_shipping_cost;

                            } else {
                                if($fullCancel == true){
                                    // Added for clear discount amount for full cancellation //
                                    $_order->setBaseDiscountAmount(0);
                                    $_order->setDiscountAmount(0);
                                    $_order->setBaseShippingDiscountAmount(0);
                                    $_order->setShippingDiscountAmount(0);
                                    // Added for clear discount amount for full cancellation //

                                    $_order->setBaseShippingAmount($ack_shipping_cost);
                                    $_order->setShippingAmount($ack_shipping_cost);
                                    $shipping_cost = $ack_shipping_cost;
                                }
                            }

                            $grand_total = $sub_total+$shipping_cost;

                            $_order->setBaseGrandTotal($grand_total);
                            $_order->setGrandTotal($grand_total);

                            $email_order_status = '';

                            if($fullCancel == true){

                                $email_order_status = 'Cancelled';

                                $this->orderManagement->cancel($orderId);

                                //$orderState = \Magento\Sales\Model\Order::STATE_CANCELED;
                                //$orderState = 'canceled';

                                $orderState = \Magento\Sales\Model\Order::STATE_CANCELED;
                                $_order->setState($orderState)->setStatus($orderState);

                                $history = $this->orderHistoryFactory->create()
                                    ->setState($orderState)->setStatus($orderState) // Update status when passing $comment parameter
                                    ->setEntityName(\Magento\Sales\Model\Order::ENTITY) // Set the entity name for order
                                    ->setComment(
                                       __('Item(s) canceled and Reason: %1.', $cancelComment)
                                ); // Set your comment
                            } else {
                                $email_order_status = 'Partially Cancelled';
                                $history = $this->orderHistoryFactory->create()
                                    ->setStatus($_order->getStatus()) // Update status when passing $comment parameter
                                    ->setEntityName(\Magento\Sales\Model\Order::ENTITY) // Set the entity name for order
                                    ->setComment(
                                       __('Item(s) cancelled and Reason: %1.', $cancelComment)
                                ); // Set your comment
                            }

                            $history->setIsCustomerNotified(true)// Enable Notify your customers via email
                                   ->setIsVisibleOnFront(true);// Enable order comment visible on sales order details

                            $_order->addStatusHistory($history); // Add your comment to order
                            $this->_orderRepository->save($_order);

                            //$_order->sendOrderUpdateEmail($notify = true, $cancelComment);

                            $this->bellNotificationHelper->pushToNotification($_order->getId(),$_order->getCustomerId(),'Sales Order',$MagentoID.' - Status of your order number has changed to '.$email_order_status);

                            $this->pushNotification->sendPushNotification('order','Order Status Updated','Order #'.$MagentoID.' - Status has changed to '.$email_order_status, $_order->getCustomerId());

                            $this->inlineTranslation->suspend();

                            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                            $sender = [
                                'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                                'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                            ];

                            $templateVars = [
                                'customer_name' => 'Admin',
                                'order_status' => $email_order_status,
                                'order_number' => $_order->getIncrementId(),
                                'sap_order_number' => $IDOCdocumentnumber,
                                'po_number' => $_order->getPayment()->getPoNumber()
                            ];
                            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                            $to_emails = explode(',', $this->helper->getToEmails());
                            $transport =
                                $this->_transportBuilder
                                ->setTemplateIdentifier('21') // Send the ID of Email template which is created in Admin panel
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
                            //echo '<pre>'.print_r($sender, true).'</pre>';

                            $this->inlineTranslation->suspend();

                            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                            $sender = [
                                'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                                'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                            ];

                            $templateVars = [
                                'customer_name' => $_order->getCustomerFirstname().' '.$_order->getCustomerLastname(),
                                'order_status' => $email_order_status,
                                'order_number' => $_order->getIncrementId(),
                                'sap_order_number' => $IDOCdocumentnumber,
                                'po_number' => $_order->getPayment()->getPoNumber()
                            ];
                            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                            $transport =
                                $this->_transportBuilder
                                ->setTemplateIdentifier('21') // Send the ID of Email template which is created in Admin panel
                                ->setTemplateOptions(
                                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                                )
                                ->setTemplateVars($templateVars)
                                ->setFrom($sender)
                                ->addTo($_order->getCustomerEmail())
                                ->getTransport();
                            $transport->sendMessage();
                            $this->inlineTranslation->resume();
                            return $this->getResponseArray(true, $MagentoID, $IDOCdocumentnumber, '');

                        }

                    } else { // ACK

                        try {
                            //$orderObject = $this->_orderRepository->get($MagentoID);
                            $orderObject1 = $this->_orderInterface->loadByIncrementId($MagentoID);

                            //echo $orderObject1->getState();

                            if($orderObject1->getState() == 'new') {
                                //echo $orderObject1->getId();
                                $orderObject = $this->_orderRepository->get($orderObject1->getId());
                                //$this->_logger->log('ERROR','ACK DEBUG MAGNR: ',[$MagentoID]);
                                //$this->_logger->log('ERROR','ACK DEBUG SAPID: ',[$IDOCdocumentnumber]);
                                //echo $order->getId();
                                $orderObject->setSapId($IDOCdocumentnumber);
                                $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
                                $orderObject->setState($orderState)->setStatus($orderState);

                                $eMessage = sprintf("Order created with number: %s", $IDOCdocumentnumber);
                                $orderObject->addCommentToStatusHistory($eMessage);
                                $orderObject->save();

                                $connection->query('UPDATE sales_order_grid SET sap_id="'.$IDOCdocumentnumber.'" WHERE entity_id = "'.(int)$orderObject->getId().'"');
                                $connection->query('UPDATE sales_order SET sap_id="'.$IDOCdocumentnumber.'" WHERE entity_id = "'.(int)$orderObject->getId().'"');

                                $this->bellNotificationHelper->pushToNotification($orderObject->getId(),$orderObject->getCustomerId(),'Sales Order',$MagentoID.' - Status of your order number has changed to Order Created');

                                $this->pushNotification->sendPushNotification('order','Order Status Updated','Order #'.$MagentoID.' - Status of your order number has changed to Order Created', $orderObject->getCustomerId());

                                $this->inlineTranslation->suspend();

                                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                                $sender = [
                                    'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                                ];

                                $templateVars = [
                                    'customer_name' => 'Admin',
                                    'order_status' => 'Order Created',
                                    'order_number' => $orderObject->getIncrementId(),
                                    'sap_order_number' => $IDOCdocumentnumber,
                                    'po_number' => $orderObject->getPayment()->getPoNumber()
                                ];
                                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                                $to_emails = explode(',', $this->helper->getToEmails());
                                $transport =
                                    $this->_transportBuilder
                                    ->setTemplateIdentifier('21') // Send the ID of Email template which is created in Admin panel
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

                                $this->inlineTranslation->suspend();

                                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                                $sender = [
                                    'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                                ];

                                $templateVars = [
                                    'customer_name' => $orderObject->getCustomerFirstname().' '.$orderObject->getCustomerLastname(),
                                    'order_status' => 'Order Created',
                                    'order_number' => $orderObject->getIncrementId(),
                                    'sap_order_number' => $IDOCdocumentnumber,
                                    'po_number' => $orderObject->getPayment()->getPoNumber()
                                ];
                                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                                $transport =
                                    $this->_transportBuilder
                                    ->setTemplateIdentifier('21') // Send the ID of Email template which is created in Admin panel
                                    ->setTemplateOptions(
                                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                                    )
                                    ->setTemplateVars($templateVars)
                                    ->setFrom($sender)
                                    ->addTo($orderObject->getCustomerEmail())
                                    ->getTransport();
                                $transport->sendMessage();
                                $this->inlineTranslation->resume();
                                return $this->getResponseArray(true, $MagentoID, $IDOCdocumentnumber, $eMessage);
                            } else {

                                $eMessage = 'Duplicate ACK Received';

                                $this->inlineTranslation->suspend();

                                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                                $sender = [
                                    'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                                ];

                                $templateVars = [
                                    'magento_order_id' => $MagentoID,
                                    'sap_order_id' => $IDOCdocumentnumber,
                                    'error_msg' => $eMessage
                                ];
                                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                                $to_emails = explode(',', $this->helper->getToEmails());

                                //echo '<pre>'.print_r($to_emails, true).'</pre>';
                                $transport =
                                    $this->_transportBuilder
                                    ->setTemplateIdentifier($this->helper->emailAckDuplicateTemplate()) // Send the ID of Email template which is created in Admin panel
                                    ->setTemplateOptions(
                                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                                    )
                                    ->setTemplateVars($templateVars)
                                    ->setFrom($sender)
                                    ->addTo($to_emails)
                                    ->getTransport();
                                $transport->sendMessage();
                                $this->inlineTranslation->resume();

                                $orderObject1->addCommentToStatusHistory($eMessage);
                                $this->_orderRepository->save($orderObject1);

                                return $this->getResponseArray(false, $MagentoID, $IDOCdocumentnumber, $eMessage);
                            }
                        } catch (\Exception $e) {
                            return $this->getResponseArray(false, $MagentoID, $IDOCdocumentnumber, $e->getMessage());
                        }
                    }

                }


            } catch (\Exception $e) {
                return $this->getResponseArray(false, $MagentoID, $IDOCdocumentnumber, $e->getMessage());
            }
        }
	}

    private function getResponseArray($status = false, $magentoID = 0, $idocDocumentNumber = 0, $message = '')
    {
        $returnData[] = [
            'success'=>$status,
            'MagentoID'=> $magentoID,
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
            ->setSubject('Duplicate ACk Triggered')
            //->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
            ->addTo($to_emails)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }
}
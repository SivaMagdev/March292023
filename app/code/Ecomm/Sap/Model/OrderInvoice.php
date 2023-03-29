<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\OrderInvoiceInterface;
use Ecomm\BellNotification\Helper\BellNotification;
use Ecomm\BellNotification\Helper\PushNotification;

class OrderInvoice implements OrderInvoiceInterface {

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

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $_invoiceCollectionFactory;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $_invoiceRepository;

    /**
    * @var \Magento\Sales\Model\Service\InvoiceService
    */
    protected $_invoiceService;

    protected $invoiceSender;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $_transactionFactory;

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
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Ecomm\Sap\Model\LoggerModel $loggerFactory,
        BellNotification $bellNotificationHelper,
        PushNotification $pushNotification,
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
        $this->_invoiceCollectionFactory   = $invoiceCollectionFactory;
        $this->_invoiceService             = $invoiceService;
        $this->invoiceSender               = $invoiceSender;
        $this->_transactionFactory         = $transactionFactory;
        $this->_invoiceRepository          = $invoiceRepository;
        $this->_loggerFactory  			   = $loggerFactory;
        $this->bellNotificationHelper      = $bellNotificationHelper;
        $this->pushNotification         = $pushNotification;
        $this->_logger          		   = $logger;
    }

	/**
	 * {@inheritdoc}
	 */
	public function updateInvoice()
	{
		$returnData = [];

        $this->_loggerFactory->createLog('OrderAckReq: '.$this->_request->getContent());

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
            try {

                /*$templateVars = ['response' => json_encode($requestData)];

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                //$to_emails = explode(',', $this->_helper->getToEmails());
                $to_emails[] = 'maideen.i@gmail.com';
                //$to_emails[] = 'mohamed.a.ibrahim@pwc.com';
                $transport =
                    $this->_transportBuilder
                    ->setTemplateIdentifier('19') // Send the ID of Email template which is created in Admin panel
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
                $this->inlineTranslation->resume();*/
                //echo 'email sent';
            } catch (\Exception $e) {
                \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug($e->getMessage());
            }
            $connection = $this->_resourceConnection->getConnection();
            //echo '<pre>'.print_r($requestData, true).'</pre>';
            //exit();
            foreach($requestData as $orderINVOICE){

                //echo '<pre>'.print_r($orderINVOICE, true).'</pre>'; exit();

                if($orderINVOICE->Documentheadergeneraldata){

                    //$info = $orderINVOICE->IDocReference;
                    $headergeneralinfo = $orderINVOICE->Documentheadergeneraldata;
                    $headerreferences = $orderINVOICE->Documentheaderreferencedata;

                    $InvoicedItems = $orderINVOICE->DocumentItemGeneralData;
                    $DeliveryInformation = $orderINVOICE->DeliveryInformation;

                    $ShippingCost = 0;

                    if(isset($DeliveryInformation->ShippingCost)){
                        $ShippingCost = $DeliveryInformation->ShippingCost;
                    }

                    //echo 'ShippingCost: '.$DeliveryInformation->ShippingCost; exit();


                    //$IDOCdocumentnumber = $info->IDOCdocumentnumber;
                    $InvoiceDocumentNumber = $headergeneralinfo->IDOCdocumentnumber;

                    $shipping_waiver = 0;
                    if(isset($headergeneralinfo->Shipping_Cost_Waiver)){
                        $shipping_waiver = abs($headergeneralinfo->Shipping_Cost_Waiver);
                    }

                    //echo '<pre>'.print_r($headerreferences, true).'</pre>'; exit();
                    foreach($headerreferences as $headerreference){

                        //echo $headerreference->InvoiceNumber.'<br />';

                        if($headerreference->InvoiceNumber == 002){

                            $IDOCdocumentnumber = $headerreference->IDOCdocumentnumber1;

                        }

                    }

                    $qty_invoiced = [];
                    $item_total_discount = 0;
                    if(is_array($InvoicedItems)){
                        foreach($InvoicedItems as $InvoicedItem){
                            $_product = $this->_productFactory->create()->loadByAttribute('material', (int)$InvoicedItem->MaterialID);
                            $qty_invoiced[(int)$InvoicedItem->MaterialID]['qty_invoiced'] = $InvoicedItem->Quantity;
                            $qty_invoiced[(int)$InvoicedItem->MaterialID]['ndc'] = $_product->getSku();

                            if(isset($InvoicedItem->Fixedsurchargediscountontotalgross)){
                                $qty_invoiced[(int)$InvoicedItem->MaterialID]['discount'] = abs($InvoicedItem->Fixedsurchargediscountontotalgross);

                                $item_total_discount += abs($InvoicedItem->Fixedsurchargediscountontotalgross);
                            }
                        }
                    } else {
                        $_product = $this->_productFactory->create()->loadByAttribute('material', (int)$InvoicedItems->MaterialID);
                        $qty_invoiced[(int)$InvoicedItems->MaterialID]['qty_invoiced'] = $InvoicedItems->Quantity;
                        $qty_invoiced[(int)$InvoicedItems->MaterialID]['ndc'] = $_product->getSku();

                        if(isset($InvoicedItems->Fixedsurchargediscountontotalgross)){
                            $qty_invoiced[(int)$InvoicedItems->MaterialID]['discount'] = abs($InvoicedItems->Fixedsurchargediscountontotalgross);

                            $item_total_discount = abs($InvoicedItems->Fixedsurchargediscountontotalgross);
                        }
                    }

                    //echo '<pre>'.print_r($qty_invoiced, true).'</pre>'; exit();

                    //echo $IDOCdocumentnumber . ' - ' . $InvoiceDocumentNumber;
                    //exit();

                    $entityInsert = [
                        'sap_id' => $IDOCdocumentnumber,
                        'invoice_id' => $InvoiceDocumentNumber,
                        'created_at' => date("Y-m-d H:i:s"),
                        'invoice_info' => json_encode($requestData),
                    ];
                    $connection->insert('ecomm_sap_order_invoice', $entityInsert);

                    $lastId = $connection->lastInsertId();

                    $order_info = $this->_orderCollectionFactory->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('sap_id', ['like' => $IDOCdocumentnumber])
                        ->getFirstItem()
                        ->setOrder('created_at','desc');
                    //echo 'order_id: '.$order_info['entity_id']; exit();

                    $order = $this->_orderRepository->get($order_info['entity_id']);

                    if ($order){
                        /*$invoices = $this->_invoiceCollectionFactory->create()
                          ->addAttributeToFilter('order_id', array('eq' => $order->getId()));

                        $invoices->getSelect()->limit(1);*/

                        //echo 'Order ID: '.$order->getId();

                        //echo "invoice-count:".$invoices->count(); exit();

                        /*if ((int)$invoices->count() !== 0) {
                            $invoices = $invoices->getFirstItem();
                            $invoice = $this->_invoiceRepository->get($invoices->getId());
                            return $invoice;
                        }*/

                        $magento_id = $order->getId();

                        if($order->canInvoice()) {

                            //echo 'test'; exit();

                            $invoiceItems = [];

                            $grand_total= 0;

                            foreach ($order->getAllItems() AS $orderItem) {

                                //echo $orderItem->getProductId();

                                $_productInfo = $this->_productModel->load($orderItem->getProductId());
                                //echo $_productInfo->getMaterial().'-';
                                if(isset($qty_invoiced[$_productInfo->getMaterial()])){
                                    $qtyInvoiced = $qty_invoiced[$_productInfo->getMaterial()]['qty_invoiced'];
                                    //$grand_total += $orderItem->getRowTotalInclTax();
                                    $grand_total += ($qtyInvoiced*$orderItem->getPrice());
                                } else {
                                    $qtyInvoiced = 0;
                                }

                                $invoiceItems[$orderItem->getId()] = $qtyInvoiced;

                                //echo $qtyInvoiced.'-';

                            }
                            //echo '<pre>'.print_r($invoiceItems, true).'</pre>'; exit();
                            try {

                                $grand_total = ($grand_total + $ShippingCost) - $item_total_discount;
                                $grand_total = ($grand_total - $shipping_waiver);

                                $invoice = $this->_invoiceService->prepareInvoice($order,$invoiceItems);
                                //$invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                                $invoice->register();
                                $invoice->getOrder()->setCustomerNoteNotify(false);
                                $invoice->getOrder()->setIsInProcess(true);
                                $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_OPEN);
                                $invoice->setShippingAmount($ShippingCost);
                                $invoice->setBaseShippingAmount($ShippingCost);
                                $invoice->setBaseShippingInclTax($ShippingCost);
                                if($shipping_waiver > 0){

                                    $discount_amount = -1*($shipping_waiver + $item_total_discount);
                                    $invoice->setDiscountAmount($discount_amount);
                                    $invoice->setBaseDiscountAmount($discount_amount);
                                }

                                $invoice->setShippingInclTax($ShippingCost);
                                $invoice->setGrandTotal($grand_total);
                                $invoice->setBaseGrandTotal($grand_total);
                                $order->addStatusHistoryComment(__('INVOICED'), false);
                                $transactionSave = $this->_transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                                $transactionSave->save();

                                $this->invoiceSender->send($invoice);

                                $m_invoice_id = $invoice->getId();

                                $entityUpdate = [
                                    'magento_id' => $magento_id,
                                    'm_invoice_id' => $m_invoice_id
                                ];

                                $connection->update(
                                    'ecomm_sap_order_invoice',
                                    $entityUpdate,
                                    ['id = ?' => (int)$lastId]
                                );
                                $params = [
                                    'customer_name' => 'Admin',
                                    'order_status' => 'Invoiced',
                                    'order_number' => $order->getIncrementId(),
                                    'sap_order_number' => $IDOCdocumentnumber,
                                    'po_number' => $order->getPayment()->getPoNumber()
                                ];

                                //echo '<pre>'.print_r($params, true).'</pre>';
                                $this->bellNotificationHelper->pushToNotification($order->getId(),$order->getCustomerId(),'Sales Order',$order->getIncrementId().' - Status of your order number has changed to Invoiced');

                                $this->pushNotification->sendPushNotification('order', 'Order Status Updated', 'Order #'.$order->getIncrementId().' - Invoiced', $order->getCustomerId());

                                //$this->sendAdminNotification($params);
                            } catch (\Exception $e) {

                                $returnData[] = [
                                    'success'=>false,
                                    'InvoiceDocumentNumber' => $InvoiceDocumentNumber,
                                    'IDOCdocumentnumber' => $IDOCdocumentnumber,
                                    'msg' => $e->getMessage()

                                ];$params = [
                                    'success'=>false,
                                    'InvoiceDocumentNumber' => $InvoiceDocumentNumber,
                                    'IDOCdocumentnumber' => $IDOCdocumentnumber,
                                    'msg' => $e->getMessage()

                                ];

                                $this->sendErrorNotification($params);
                                return $returnData;

                            }

                            //return $invoice;


                        }
                    }

                }

            }

            if($IDOCdocumentnumber == 0){

                $returnData[] = [
                    'success'=>fasle,
                    'InvoiceDocumentNumber' => $InvoiceDocumentNumber,
                    'IDOCdocumentnumber' => $IDOCdocumentnumber,
                    'msg' => 'Invalid Sales order Number'

                ];
                $params = [
                    'success'=>false,
                    'InvoiceDocumentNumber' => $InvoiceDocumentNumber,
                    'IDOCdocumentnumber' => $IDOCdocumentnumber,
                    'msg' => 'Invalid Sales order Number'

                ];

                $this->sendErrorNotification($params);
            } else {
                $returnData[] = [
                    'success'=>true,
                    'InvoiceDocumentNumber' => $InvoiceDocumentNumber,
                    'IDOCdocumentnumber' => $IDOCdocumentnumber,
                    'msg' => ''

                ];
            }
        }

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
            ->setTemplateIdentifier('29') // Send the ID of Email template which is created in Admin panel
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
            ->setTemplateIdentifier('27') // Send the ID of Email template which is created in Admin panel
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
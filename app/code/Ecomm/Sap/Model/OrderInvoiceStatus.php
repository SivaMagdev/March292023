<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\OrderInvoiceStatusInterface;
use Ecomm\BellNotification\Helper\BellNotification;
use Ecomm\BellNotification\Helper\PushNotification;

class OrderInvoiceStatus implements OrderInvoiceStatusInterface {

    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $_orderRepository;

    protected $_helper;

    public $_request;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $_invoiceCollectionFactory;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $_invoiceRepository;

    protected $resourceConnection;

    protected $_loggerFactory;

    protected $bellNotificationHelper;

    protected $pushNotification;

    protected $_logger;

	public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ecomm\Sap\Model\LoggerModel $loggerFactory,
        BellNotification $bellNotificationHelper,
        PushNotification $pushNotification,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_transportBuilder           = $transportBuilder;
        $this->inlineTranslation           = $inlineTranslation;
        $this->scopeConfig                 = $scopeConfig;
        $this->_orderRepository            = $orderRepository;
        $this->_helper                     = $helper;
        $this->_request         		   = $request;
        $this->_invoiceCollectionFactory   = $invoiceCollectionFactory;
        $this->_invoiceRepository          = $invoiceRepository;
        $this->resourceConnection          = $resourceConnection;
        $this->_loggerFactory  			   = $loggerFactory;
        $this->bellNotificationHelper      = $bellNotificationHelper;
        $this->pushNotification            = $pushNotification;
        $this->_logger          		   = $logger;
    }

	/**
	 * {@inheritdoc}
	 */
	public function updateInvoice()
	{
		$returnData = [];

        $this->_loggerFactory->createLog('InvoiceStatusReq: '.$this->_request->getContent());

        $this->inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sender = [
            'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];

        $templateVars = [];

        $this->_logger->critical('InvoiceStatusReq', ['data' => $this->_request->getContent()]);

        $request_content = str_replace('ns1:','',$this->_request->getContent());

        $requestData = json_decode($request_content);

        if($requestData){

            $connection = $this->resourceConnection->getConnection();

            /*try {

                $templateVars = ['response' => $this->_request->getContent()];

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                //$to_emails = explode(',', $this->_helper->getToEmails());
                $to_emails[] = 'testuser2.pwc@gmail.com';
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
                $this->inlineTranslation->resume();
                //echo 'email sent';
            } catch (\Exception $e) {
                \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug($e->getMessage());
            }*/

            //echo '<pre>'.print_r($requestData->MT_PaymentStatus->PaymentStatus, true).'</pre>'; exit();
            if($requestData->MT_PaymentStatus->PaymentStatus){
                foreach($requestData->MT_PaymentStatus->PaymentStatus as $PaymentStatus){

                    //echo '<pre>'.print_r($PaymentStatus, true).'</pre>';

                    //echo $PaymentStatus->SalesDocument;
                    //echo $PaymentStatus->BillingDocument;
                    //echo $PaymentStatus->ECOMPaymentStatus;

                    //if($PaymentStatus->ECOMPaymentStatus = 'Payment Received'){}

                    if($PaymentStatus->ClearingDocument != '') {

                        $select = $connection->select()
                        ->from(['si' => 'ecomm_sap_order_invoice'], ['*'])
                        ->where("si.invoice_id = :invoice_id");
                        $bind = ['invoice_id'=>$PaymentStatus->BillingDocument];
                        $data = $connection->fetchRow($select, $bind);

                        //echo 'm_invoice_id: '.$data['m_invoice_id'];

                        if(isset($data['m_invoice_id']) && (int)$data['m_invoice_id'] > 0) {

                            $invoice = $this->_invoiceRepository->get($data['m_invoice_id']);

                            //echo $invoice->getOrderId();

                            $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);

                            $this->_invoiceRepository->save($invoice);

                            $order = $this->_orderRepository->get($invoice->getOrderId());

                             $this->bellNotificationHelper->pushToNotification($order->getId(),$order->getCustomerId(),'Sales Order',$order->getIncrementId().' - Status of your invoice number has changed to paid');

                                $this->pushNotification->sendPushNotification('order', 'Payment Status Updated','Order #'.$order->getIncrementId().' - invoice paid', $order->getCustomerId());

                            $returnData[] = [
                                'success'=>true,
                                'SalesDocument' => $PaymentStatus->SalesDocument,
                                'BillingDocument' => $PaymentStatus->BillingDocument,
                                'msg' => 'Invoice Status Updated'

                            ];
                        }
                    }


                }
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
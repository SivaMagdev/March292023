<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\OrderAsnPrintInterface;

class OrderAsnPrint implements OrderAsnPrintInterface {

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
        $this->_logger          		= $logger;
    }

	/**
	 * {@inheritdoc}
	 */
	public function updateAsn()
	{
		$returnData = [];

        $this->_loggerFactory->createLog('OrderASNPrintReq: '.$this->_request->getContent());

        $this->inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sender = [
            'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];

        $templateVars = [];

        //$this->_logger->critical('ProductStock', ['data' => $this->_request->getContent()]);

        $request_conetent = str_replace('ns0:c','',$this->_request->getContent());

        $requestData = json_decode($request_conetent);
        $DeliverDocumentNumber = 0;

        if($requestData){
        	$connection = $this->_resourceConnection->getConnection();
            //echo '<pre>'.print_r($requestData, true).'</pre>'; exit();
            foreach($requestData as $PedigreeXML){
                //echo '<pre>'.print_r($PedigreeXML->Pedigree, true).'</pre>'; exit();
                if(is_array($PedigreeXML->Pedigree)){
                    foreach($PedigreeXML->Pedigree as $InitialPedigree){

                        $DeliverDocumentNumber = $InitialPedigree->TransactionInfo->TransactionIdentifier->Identifier;

                        //echo '<pre>'.print_r($InitialPedigree->TransactionInfo->TransactionIdentifier->Identifier, true).'</pre>';

                        break;
                    }
                } else {
                    $DeliverDocumentNumber = $PedigreeXML->Pedigree->TransactionInfo->TransactionIdentifier->Identifier;
                }
            }

            //echo $DeliverDocumentNumber;

            if($DeliverDocumentNumber == 0){
                $returnData[] = [
                    'success'=>false,
                    'DeliverDocumentNumber' => '',
                    'msg' =>'In JSON DeliverDocumentNumber fied not identified'

                ];
            } else {
                $returnData[] = [
                    'success'=>true,
                    'DeliverDocumentNumber' => $DeliverDocumentNumber,
                    'msg' =>'Delivery print info updated'

                ];
            }

            $entityInsert = [
                'delivery_id' => $DeliverDocumentNumber,
                'created_at' => date("Y-m-d H:i:s"),
                'asn_info' => $this->_request->getContent(),
            ];
            $connection->insert('ecomm_sap_order_asnprint', $entityInsert);

            $lastId = $connection->lastInsertId();

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
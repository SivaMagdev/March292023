<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\OrderCancelInterface;

class OrderCancel implements OrderCancelInterface {

    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $_helper;

    public $_request;

    protected $_orderFactory;

    protected $_orderRepository;

    protected $orderHistoryFactory;

    protected $_resourceConnection;

    protected $_orderCollectionFactory;

    protected $_productFactory;

    protected $_productModel;

    protected $orderManagement;

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
        \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
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
        $this->orderHistoryFactory      = $orderHistoryFactory;
        $this->_resourceConnection      = $resourceConnection;
        $this->_orderCollectionFactory  = $orderCollectionFactory;
        $this->_productFactory          = $productFactory;
        $this->_productModel            = $productModel;
        $this->orderManagement          = $orderManagement;
        $this->_loggerFactory  			= $loggerFactory;
        $this->_logger          		= $logger;
    }

	/**
	 * {@inheritdoc}
	 */
	public function cancelOrder()
	{
		$returnData = [];

        $this->_loggerFactory->createLog('OrderCancelReq: '.$this->_request->getContent());

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

            foreach($requestData as $orderCancel){

                //echo '<pre>'.print_r($orderCancel, true).'</pre>'; exit();

                $IDOCdocumentnumber = $orderCancel->Documentheaderreferencedata->IDOCdocumentnumber;
                $MagentoID = $orderCancel->Documentheaderreferencedata->MagentoID;

                $CancelledItems = $orderCancel->DeliveryItem;

                echo $IDOCdocumentnumber.' - '.$MagentoID.'-';

                if($IDOCdocumentnumber){

                    $qty_cancelled = [];
                    if(is_array($CancelledItems)){
                        foreach($CancelledItems as $CancelledItem){
                            $_product = $this->_productFactory->create()->loadByAttribute('material', (int)$CancelledItem->MaterialNumber);
                            $qty_cancelled[(int)$CancelledItem->MaterialNumber]['qty_cancelled'] = $CancelledItem->Actualquantitydelivered;
                            $qty_cancelled[(int)$CancelledItem->MaterialNumber]['ndc'] = $_product->getSku();
                        }
                    } else {
                        $_product = $this->_productFactory->create()->loadByAttribute('material', (int)$CancelledItems->MaterialNumber);
                        $qty_cancelled[(int)$CancelledItems->MaterialNumber]['qty_cancelled'] = $CancelledItems->Actualquantitydelivered;
                        $qty_cancelled[(int)$CancelledItems->MaterialNumber]['ndc'] = $_product->getSku();
                    }

                    //echo '<pre>'.print_r($qty_cancelled, true).'</pre>'; exit();

                    if(isset($orderCancel->ORDERCancellation->CancelReason)){
                        $CancelReason = $orderCancel->ORDERCancellation->CancelReason;
                    } else {
                        $CancelReason = '';
                    }


                    try {

                        $entityInsert = [
                            'sap_id' => $IDOCdocumentnumber,
                            'order_id' => $MagentoID,
                            'created_at' => date("Y-m-d H:i:s"),
                            'cancel_info' => json_encode($requestData)
                        ];
                        $connection->insert('ecomm_sap_order_cancel', $entityInsert);

                        $lastId = $connection->lastInsertId();

                        //echo $lastId.'-';

                        $order_info = $this->_orderCollectionFactory->create()
                            ->addFieldToSelect('*')
                            ->addFieldToFilter('sap_id', ['like' => $IDOCdocumentnumber])
                            ->getFirstItem()
                            ->setOrder('created_at','desc');

                       // echo 'order_id: '.$order_info['entity_id']; exit();

                        $_order = $this->_orderRepository->get($order_info['entity_id']);

                        $orderId = $_order->getId();

                        //echo $orderId;



                        if ($_order->canCancel()) {
                            foreach ($_order->getAllItems() as $orderItem) {

                                $_productInfo = $this->_productModel->load($orderItem->getProductId());
                                if(isset($qty_cancelled[$_productInfo->getMaterial()])){
                                    $qtyCancelled = $qty_cancelled[$_productInfo->getMaterial()]['qty_cancelled'];
                                } else {
                                    $qtyCancelled = 0;
                                }
                                if($qtyCancelled >0) {
                                    $orderItem->setQtyCanceled($qtyCancelled);
                                    $orderItem->save();
                                }
                            }
                            $history = $this->orderHistoryFactory->create()
                                ->setStatus($_order->getStatus()) // Update status when passing $comment parameter
                                ->setEntityName(\Magento\Sales\Model\Order::ENTITY) // Set the entity name for order
                                ->setComment(
                                   __('Cancel Item Reason: %1.', $CancelReason)
                            ); // Set your comment

                           $history->setIsCustomerNotified(true)// Enable Notify your customers via email
                                   ->setIsVisibleOnFront(true);// Enable order comment visible on sales order details

                           $_order->addStatusHistory($history); // Add your comment to order
                           $this->_orderRepository->save($_order);
                        }



                        //-----------------------FULL CANCELLATION-----------------------------

                        /*$this->orderManagement->cancel($orderId);

                        if(isset($orderCancel->ORDERCancellation->CancelReason)){
                            $CancelReason = $orderCancel->ORDERCancellation->CancelReason;
                        } else {
                            $CancelReason = '';
                        }

                        $orderState = \Magento\Sales\Model\Order::STATE_CANCELED;

                        $history = $this->orderHistoryFactory->create()
                            ->setState($orderState)->setStatus($orderState) // Update status when passing $comment parameter
                            ->setEntityName(\Magento\Sales\Model\Order::ENTITY) // Set the entity name for order
                            ->setComment(
                               __('Cancel Reason: %1.', $CancelReason)
                        ); // Set your comment

                       $history->setIsCustomerNotified(true)// Enable Notify your customers via email
                               ->setIsVisibleOnFront(true);// Enable order comment visible on sales order details

                       $_order->addStatusHistory($history); // Add your comment to order
                       $this->_orderRepository->save($_order);*/

                        //echo '<pre>'.print_r($orderCancel->ORDERCancellation, true).'</pre>'; exit();

                    } catch (\Exception $e) {
                        $returnData[] = [
                            'success'=>false,
                            'IDOCdocumentnumber' => $IDOCdocumentnumber,
                            'MagentoID'=> $MagentoID,
                            'msg' => $e->getMessage()
                        ];

                        $this->_loggerFactory->createLog('OrderCancelReq: '.$e->getMessage());

                        return $returnData;
                    }
                }
            }


            if($IDOCdocumentnumber == 0){

                $returnData[] = [
                    'success'=>false,
                    'IDOCdocumentnumber' => $IDOCdocumentnumber,
                    'MagentoID' => $MagentoID,
                    'msg' => ''

                ];
            } else {
                $returnData[] = [
                    'success'=>true,
                    'IDOCdocumentnumber' => $IDOCdocumentnumber,
                    'MagentoID' => $MagentoID,
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
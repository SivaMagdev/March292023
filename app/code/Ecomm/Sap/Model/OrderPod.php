<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\OrderPodStatusInterface;
use Magento\Framework\App\RequestInterface;
use Ecomm\Sap\Model\SalesOrderPodExtensionFactory;
use Ecomm\Sap\Model\ResourceModel\SalesOrderPodExtension\CollectionFactory as PodCollectionFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class OrderPod implements OrderPodStatusInterface {

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var SalesOrderPodExtensionFactory
     */
    private $salesOrderPodExtensionFactory;

    /**
     * @var PodCollectionFactory
     */
    private $podCollectionFactory;

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TimezoneInterface
     */
    private $timeZone;

    /**
     * @var LoggerInterface
     */
    private $logger;

	/**
     * @param RequestInterface $request
     * @param SalesOrderPodExtensionFactory $salesOrderPodExtensionFactory
     * @param CollectionFactory $orderCollectionFactory
     * @param PodCollectionFactory $podCollectionFactory
     * @param OrderRepository $orderRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTime $dateTime
     * @param TimezoneInterface $timeZone
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        SalesOrderPodExtensionFactory $salesOrderPodExtensionFactory,
        CollectionFactory $orderCollectionFactory,
        PodCollectionFactory $podCollectionFactory,
        OrderRepository $orderRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTime $dateTime,
        TimezoneInterface $timeZone,
        LoggerInterface $logger
    )
    {
        $this->request = $request;
        $this->salesOrderPodExtensionFactory = $salesOrderPodExtensionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->podCollectionFactory = $podCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTime = $dateTime;
        $this->timeZone = $timeZone;
    }

	/**
	 * {@inheritdoc}
	 */
	public function getPayload()
	{
		$returnData = [];

        $writer = new \Laminas\Log\Writer\Stream(BP . '/var/log/pod_log.log');
        $logger = new  \Laminas\Log\Logger();
        $logger->addWriter($writer);

        $logger->info('Payload: '.$this->request->getContent());

        try {
            $salesOrderId = 0;
            $deliveryId = 0;

            $requestData = json_decode($this->request->getContent());

            $salesOrderId = isset($requestData->POD->VBELV) ? $requestData->POD->VBELV : 0;
            $deliveryId = isset($requestData->POD->VBELN) ? $requestData->POD->VBELN : 0;

            $podId = $this->getPodId($salesOrderId, $deliveryId);

            if ($salesOrderId == 0 || empty($salesOrderId)) {
                $eMessage = 'Sales Order ID is Invalid';
                return $this->getResponseArray(false, $salesOrderId, $deliveryId, $eMessage);
            }

            if ($deliveryId == 0 || empty($deliveryId)) {
                $eMessage = 'Delivery ID is Invalid';
                return $this->getResponseArray(false, $salesOrderId, $deliveryId, $eMessage);
            }

            $orderInfo = $this->orderCollectionFactory->create()
                ->addFieldToSelect('entity_id')
                ->addFieldToFilter('sap_id', ['like' => $requestData->POD->VBELV])
                ->getFirstItem()
                ->setOrder('created_at','desc');
            $order = $this->orderRepository->get($orderInfo['entity_id']);
            if ($order) {

                $status = 'In Transit';
                $orderStatusCode = 'in_transit';
                $currentDate = $this->timeZone->date($this->dateTime->date('Y-m-d H:i:s'))->format('Y-m-d');
                $currentDate = strtotime($currentDate);
                $podDate = isset($requestData->POD->PODAT) ? $requestData->POD->PODAT : '';
                if ($podDate != '') {
                    $podDate = strtotime($podDate);

                    if ($podDate <= $currentDate) {
                        $status = 'Delivered';
                        $orderStatusCode = 'delivered';
                        /**
                         * Prepare Order extension data
                         */
                        $prepareData = $this->prepareData($podId, $requestData, $status);
                        $eMessage = sprintf("Tracking URL recieved for deliveryId: %s", $requestData->POD->VBELN);

                        /**
                         * Save POD extension data
                         */
                        $orderPodExtModel = $this->salesOrderPodExtensionFactory->create();
                        $orderPodExtModel->setData($prepareData);
                        $orderPodExtModel->save();
                        $shipments = $this->getShipmentDataByOrderId($order->getId());
                        $deliveryCount = count($shipments);
                        if ($deliveryCount > 1) {
                            $podList = $this->podCollectionFactory->create()
                            ->addFieldToSelect('entity_id')
                            ->addFieldToFilter('sales_order_id', ['like' => $requestData->POD->VBELV])
                            ->addFieldToFilter('status', ['like' => 'Delivered'])
                            ->setOrder('updated_at','desc');
                            $deliveredCount = 0;
                            $deliveredCount = $podList->getSize();
                            if(count($shipments) != $deliveredCount) {
                                $orderStatusCode = 'partially_delivered';
                            }
                        }

                    }
                } else {
                    /**
                     * Prepare Order extension data
                     */
                    $prepareData = $this->prepareData($podId, $requestData, $status);
                    $eMessage = sprintf("Tracking URL recieved for deliveryId: %s", $requestData->POD->VBELN);

                    /**
                     * Save POD extension data
                     */
                    $orderPodExtModel = $this->salesOrderPodExtensionFactory->create();
                    $orderPodExtModel->setData($prepareData);
                    $orderPodExtModel->save();
                }

                $orderState = Order::STATE_COMPLETE;
                $order->setState($orderState)->setStatus($orderStatusCode);
                $order->addCommentToStatusHistory($eMessage);
                $order->save();

                return $this->getResponseArray(true, $salesOrderId, $deliveryId, $eMessage);
            } else {
                $eMessage = 'Sales Order does not exist';
                return $this->getResponseArray(false, $salesOrderId, $deliveryId, $eMessage);
            }
        } catch (\exception $e) {
            return $this->getResponseArray(false, $salesOrderId, $deliveryId, $e->getMessage());
        }
	}

    /**
     * Prepare data to save
     *
     * @param array $requestData
     * @param string $status
     * @return array
     */
    private function prepareData($podId, $requestData, $status)
    {
        $prepareData = [
            'track_id' => $requestData->POD->TRACKID,
            'delivery_id' => $requestData->POD->VBELN,
            'carrier_code' => $requestData->POD->CARRIER_CODE,
            'delivery_date' => $requestData->POD->BLDAT,
            'delivery_time' => $requestData->POD->ERZET,
            'sales_order_id' => $requestData->POD->VBELV,
            'po_number' => $requestData->POD->BSTNK,
            'po_date' => $requestData->POD->BSTDK,
            'estimated_delivery_date' => $requestData->POD->VDATU,
            'pod_date' => $requestData->POD->PODAT,
            'pod_time' => $requestData->POD->POD_TIME,
            'soldto' => $requestData->POD->KUNNR,
            'soldto_name' => $requestData->POD->NAME1,
            'shipto' => $requestData->POD->KUNNR1,
            'shipto_name' => $requestData->POD->NAME11,
            'tracking_link' => $requestData->POD->LINK,
            'shipment_date' => $requestData->POD->TRDAT,
            'shipment_time' => $requestData->POD->TRNTM,
            'status' => $status
        ];

        if ($podId) {
            $prepareData = array_merge($prepareData, ['entity_id' => $podId]);
        }
        return $prepareData;
    }

    private function getResponseArray($status = false, $orderId = 0, $deliveryId = 0, $message = '')
    {
        $returnData[] = [
            'success'=>$status,
            'VBELV'=> $orderId,
            'VBELN' => $deliveryId,
            'msg' => $message

        ];
        return $returnData;
    }

    private function getPodId($salesOrderId, $deliveryId)
    {
        $pod = $this->podCollectionFactory->create()
            ->addFieldToSelect('entity_id')
            ->addFieldToFilter('sales_order_id', ['like' => $salesOrderId])
            ->addFieldToFilter('delivery_id', ['like' => $deliveryId])
            ->getFirstItem()
            ->setOrder('updated_at','desc');
        return $pod['entity_id'];
    }

    /**
     * Shipment by Order id
     *
     * @param int $orderId
     * @return ShipmentInterface[]|null |null
     */
    public function getShipmentDataByOrderId(int $orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)->create();
        try {
            $shipments = $this->shipmentRepository->getList($searchCriteria);
            $shipmentRecords = $shipments->getItems();
        } catch (Exception $exception)  {
            $this->logger->critical($exception->getMessage());
            $shipmentRecords = null;
        }
        return $shipmentRecords;
    }
}

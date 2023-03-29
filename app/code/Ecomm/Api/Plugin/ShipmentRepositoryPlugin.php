<?php

namespace Ecomm\Api\Plugin;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\Sales\Api\Data\ShipmentExtensionInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentSearchResultInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;

/**
 * Class ShipmentRepositoryPlugin
 */
class ShipmentRepositoryPlugin
{
    /**
     * Shipment Extension Attributes Factory
     *
     * @var ShipmentExtensionFactory
     */
    protected $extensionFactory;

    protected $_resourceConnection;

    protected $_orderRepository;

    public function __construct(
        ShipmentExtensionFactory $extensionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Model\OrderRepository $orderRepository
    )
    {
        $this->extensionFactory = $extensionFactory;
        $this->_resourceConnection      = $resourceConnection;
        $this->_orderRepository         = $orderRepository;
    }

     /**
     *
     * @param ShipmentRepositoryInterface $subject
     * @param ShipmentInterface $shipment
     *
     * @return ShipmentInterface
     */
    public function afterGet(ShipmentRepositoryInterface $subject, ShipmentInterface $shipment)
    {
        $extensionAttributes = $shipment->getExtensionAttributes();

        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();

        if ($extensionAttributes) {
            $connection = $this->_resourceConnection->getConnection();
            $select = $connection->select()
            ->from(['si' => 'ecomm_sap_order_asn'], ['*'])
            ->where("si.m_delivery_id = :m_delivery_id");
            $bind = ['m_delivery_id'=>$shipment->getId()];

            $data = $connection->fetchRow($select, $bind);
            if(isset($data['delivery_id'])){
                $extensionAttributes->setDeliveryIdocNumber((int)$data['delivery_id']);
            } else {
                $extensionAttributes->setDeliveryIdocNumber('');
            }

            $_order = $this->_orderRepository->get($shipment->getOrderId());

            $podInfo = $this->getPodDetails($shipment->getId());
            $podDateTime = '';
            $podTrakingUrl = '';
            if(isset($podInfo['pod_date']) && $podInfo['pod_date'] != '') {
                $podDateTime = $podInfo['pod_date'];
            }
            if(isset($podInfo['pod_time']) && $podInfo['pod_time'] != '') {
                $podDateTime .= ' '.$podInfo['pod_time'];
            }
            if(isset($podInfo['tracking_link']) && $podInfo['tracking_link'] != '') {
                $podTrakingUrl = $podInfo['tracking_link'];
            }

            $extensionAttributes->setSapId($_order->getSapId());
            $extensionAttributes->setOrderIncrementId($_order->getIncrementId());
            $extensionAttributes->setRgddDeliveryDate($_order->getRgddDeliveryDate());
            $extensionAttributes->setRgddDeliveryComment($_order->getRgddDeliveryComment());
            $extensionAttributes->setPodDatetime($podDateTime);
            $extensionAttributes->setPodTrackingUrl($podTrakingUrl);
            $shipment->setExtensionAttributes($extensionAttributes);
        }

        return $shipment;
    }

    public function afterGetList(
        \Magento\Sales\Api\ShipmentRepositoryInterface $subject,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection $resultShipment
    ) {
        foreach ($resultShipment->getItems() as $shipment) {
            $this->afterGet($subject, $shipment);
        }

        return $resultShipment;
    }

    private function getPodDetails($shippment_id){

        $data = [];

        $connection = $this->_resourceConnection->getConnection();

        $select = $connection->select()
        ->from(['si' => 'ecomm_sap_order_asn'], ['spod.*'])
        ->joinLeft(
            ['spod' => 'ecomm_sales_order_pod_ext'],
            'spod.delivery_id = si.delivery_id')
        ->where("si.m_delivery_id = :m_delivery_id");
        $bind = ['m_delivery_id'=>$shippment_id];

        $data = $connection->fetchRow($select, $bind);

        if(isset($data)) {
            return $data;
        } else {
            return [];
        }
    }
}
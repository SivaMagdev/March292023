<?php

namespace Ecomm\Sap\Block\Shipping;


class Items extends \Magento\Shipping\Block\Items
{
	/**
     * Get html of shipment comments block
     *
     * @param   \Magento\Sales\Model\Order\Shipment $shipment
     * @return  string
     */
    public function getCommentsHtml($shipment)
    {
        $html = '';
        $comments = $this->getChildBlock('shipment_comments');
        if ($comments) {
            $comments->setEntity($shipment)->setTitle(__('About Your Shipment'));
            $html = $comments->toHtml();
        }
        return $html;
    }

    public function getSapDeliveryId($shippment_id){

    	$data = [];

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resourceConnection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
		$connection = $resourceConnection->getConnection();

		$select = $connection->select()
		->from(['si' => 'ecomm_sap_order_asn'], ['delivery_id'])
		->where("si.m_delivery_id = :m_delivery_id");
		$bind = ['m_delivery_id'=>$shippment_id];

		$data = $connection->fetchRow($select, $bind);

        if(isset($data['delivery_id'])) {
            return (int)$data['delivery_id'];
        } else {
            return 0;
        }
    }

    public function getPodDetails($shippment_id){

        $data = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resourceConnection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $connection = $resourceConnection->getConnection();

        $select = $connection->select()
        ->from(['si' => 'ecomm_sap_order_asn'], ['spod.*'])
        ->joinLeft(
            ['spod' => 'ecomm_sales_order_pod_ext'],
            'spod.delivery_id = si.delivery_id')
        ->where("si.m_delivery_id = :m_delivery_id");
        $bind = ['m_delivery_id'=>$shippment_id];

        $data = $connection->fetchRow($select, $bind);
        //$data = $connection->fetchAll($select, $bind);

        //echo '<pre>'.print_r($data, true).'</pre>'; exit();

        if(isset($data)) {
            return $data;
        } else {
            return [];
        }
    }
}
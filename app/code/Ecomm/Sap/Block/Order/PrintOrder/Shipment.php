<?php

namespace Ecomm\Sap\Block\Order\PrintOrder;


class Shipment extends \Magento\Sales\Block\Order\PrintOrder\Shipment
{
	public function getSapShippment(){

		$data = [];

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$resourceConnection = $objectManager->get('\Magento\Framework\App\ResourceConnection');

		$connection = $resourceConnection->getConnection();

		if($this->getRequest()->getParam('shipment_id', null) != null){
			//echo $this->getRequest()->getParam('invoice_id', null);
			$select = $connection->select()
			->from(['sp' => 'ecomm_sap_order_asn'], ['sp.sap_id','sp.magento_id','spp.asn_info'])
			->join(['spp' => 'ecomm_sap_order_asnprint'],'sp.delivery_id = spp.delivery_id')
			->where("sp.m_delivery_id = :m_delivery_id")
			->group("sp.delivery_id");
			$bind = ['m_delivery_id'=>$this->getRequest()->getParam('shipment_id', null)];
		} else {
			$select = $connection->select()
			->from(['sp' => 'ecomm_sap_order_asn'], ['sp.sap_id','sp.magento_id','spp.asn_info'])
			->join(['spp' => 'ecomm_sap_order_asnprint'],'sp.delivery_id = spp.delivery_id')
			->where("sp.magento_id = :magento_id")
			->group("sp.delivery_id");
			$bind = ['magento_id'=>$this->getRequest()->getParam('order_id', null)];
		}
		//$data = $connection->fetchRow($select, $bind);
		$data = $connection->fetchAll($select, $bind);

		//echo '<pre>'.print_r($data, true).'</pre>'; exit();
		return $data;

	}


	public function getProductSKU($material_code){

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');

		$_product = $productFactory->create()->loadByAttribute('material', $material_code);

		return $_product->getSku();

	}


	public function getProductInfo($ndc){

		//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		//$productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
		//$_product = $productFactory->create()->loadByAttribute('material', $material_code);

		$sku = substr($ndc, 0, 5).'-'.substr($ndc, 5, 4).'-'.substr($ndc, 9);

		return $sku;

		//echo $sku;


		//$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
		//return $productRepository->get($sku);
		//return [];

	}
}
<?php

namespace Ecomm\Sap\Block\Order\PrintOrder;


class Invoice extends \Magento\Sales\Block\Order\PrintOrder\Invoice
{
	public function getSapInvoice(){

		$data = [];

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$resourceConnection = $objectManager->get('\Magento\Framework\App\ResourceConnection');

		$connection = $resourceConnection->getConnection();

		//echo $this->getOrder()->getSapId();

		//echo $this->getOrder()->getSapId().' - '.$this->getInvoice()->getId();
		//echo $this->getOrder()->getId().' - '.$this->getInvoice()->getId();

		//echo $this->getRequest()->getParam('invoice_id', null).'-'.$this->getRequest()->getParam('order_id', null);

		if($this->getRequest()->getParam('invoice_id', null) != null){
			//echo $this->getRequest()->getParam('invoice_id', null);
			$select = $connection->select()
			->from(['si' => 'ecomm_sap_order_invoice'], ['*'])
			->where("si.m_invoice_id = :m_invoice_id");
			$bind = ['m_invoice_id'=>$this->getRequest()->getParam('invoice_id', null)];
		} else {
			//echo $this->getRequest()->getParam('order_id', null);
			$select = $connection->select()
			->from(['si' => 'ecomm_sap_order_invoice'], ['*'])
			->where("si.magento_id = :magento_id");
			$bind = ['magento_id'=>$this->getRequest()->getParam('order_id', null)];
		}
		//$data = $connection->fetchRow($select, $bind);
		$data = $connection->fetchAll($select, $bind);

		//echo 'SELECT * FROM ecomm_sap_order_invoice WHERE sap_id="'.$this->getOrder()->getSapId().'"';

		//echo '<pre>'.print_r($data, true).'</pre>'; exit();
		return $data;

	}


	public function getProductSKU($material_code){

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');

		$_product = $productFactory->create()->loadByAttribute('material', trim($material_code));

		if($_product) {
			return $_product->getSku();
		} else {
			return $material_code;
		}

	}
}
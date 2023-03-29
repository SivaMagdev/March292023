<?php

namespace Ecomm\Sap\Block\Order\Invoice;


class Items extends \Magento\Sales\Block\Order\Invoice\Items
{
	public function getSapInvoiceId($invoice_id){

		$data = [];

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$resourceConnection = $objectManager->get('\Magento\Framework\App\ResourceConnection');

		$connection = $resourceConnection->getConnection();

		$select = $connection->select()
		->from(['si' => 'ecomm_sap_order_invoice'], ['*'])
		->where("si.m_invoice_id = :m_invoice_id");
		$bind = ['m_invoice_id'=>$invoice_id];

		$data = $connection->fetchRow($select, $bind);
		//$data = $connection->fetchAll($select, $bind);

		//echo 'SELECT * FROM ecomm_sap_order_invoice WHERE sap_id="'.$this->getOrder()->getSapId().'"';

		//echo '<pre>'.print_r($data, true).'</pre>'; exit();

		if(isset($data['invoice_id'])) {
			return $data['invoice_id'];
		} else {
			return 0;
		}

	}
}
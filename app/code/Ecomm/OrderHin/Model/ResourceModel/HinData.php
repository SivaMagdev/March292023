<?php
namespace Ecomm\OrderHin\Model\ResourceModel;


class HinData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('ecomm_sales_order_hin', 'entity_id');
	}

}
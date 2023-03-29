<?php
namespace Ecomm\PriceEngine\Model\ResourceModel;


class Stock extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('ecomm_stock', 'stock_id');
	}

}
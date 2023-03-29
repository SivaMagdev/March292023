<?php
namespace Ecomm\Api\Model\ResourceModel;


class PolicyTrack extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('ecomm_app_policy_track', 'id');
	}

}
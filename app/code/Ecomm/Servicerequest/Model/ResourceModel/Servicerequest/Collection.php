<?php

namespace Ecomm\Servicerequest\Model\ResourceModel\Servicerequest;

/**
 * This class contains order model collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

	/**
     * @var string
     * @codingStandardsIgnoreStart
     */
    protected $_idFieldName = 'id';
    /**
     * Define model & resource model
     */
    protected function _construct() {
        $this->_init ( 'Ecomm\Servicerequest\Model\Servicerequest', 'Ecomm\Servicerequest\Model\ResourceModel\Servicerequest' );
    }
}
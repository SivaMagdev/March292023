<?php

namespace Ecomm\BellNotification\Model\ResourceModel\BellNotification;

/**
 * This class contains seller model collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
    /**
     * Define model & resource model
     */
    protected function _construct() {
        $this->_init ( 'Ecomm\BellNotification\Model\BellNotification', 'Ecomm\BellNotification\Model\ResourceModel\BellNotification' );
    }
}
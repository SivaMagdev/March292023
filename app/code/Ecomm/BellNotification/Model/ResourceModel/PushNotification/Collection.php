<?php

namespace Ecomm\BellNotification\Model\ResourceModel\PushNotification;

/**
 * This class contains seller model collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
    /**
     * Define model & resource model
     */
    protected function _construct() {
        $this->_init ( 'Ecomm\BellNotification\Model\PushNotification', 'Ecomm\BellNotification\Model\ResourceModel\PushNotification' );
    }
}
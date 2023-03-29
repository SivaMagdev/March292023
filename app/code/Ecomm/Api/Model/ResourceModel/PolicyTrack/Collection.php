<?php

namespace Ecomm\Api\Model\ResourceModel\PolicyTrack;

/**
 * This class contains seller model collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
    /**
     * Define model & resource model
     */
    protected function _construct() {
        $this->_init ( 'Ecomm\Api\Model\PolicyTrack', 'Ecomm\Api\Model\ResourceModel\PolicyTrack' );
    }
}
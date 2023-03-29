<?php

namespace Ecomm\Servicerequest\Model\ResourceModel;

/**
 * This class initiates order model primary id
 */
class Servicerequest extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
    /**
     * Define order
     */
    protected function _construct() {
        $this->_init ( 'ecomm_service_request', 'id' );
    }
}

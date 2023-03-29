<?php
/**
 * @category   Ecomm
 * @package    Ecomm_LoggedCount
 */

namespace Ecomm\LoggedCount\Model\ResourceModel;

class LoggedCount extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('customer_logged_count', 'id');   //here "ecomm_loggedcount" is table name and "loggedcount_id" is the primary key of custom table
    }
}
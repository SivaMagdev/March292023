<?php
/**
 * @category   Ecomm
 * @package    Ecomm_LoggedCount
 * @author     pwc@gmail.com
 
 */

namespace Ecomm\LoggedCount\Model\ResourceModel\LoggedCount;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Ecomm\LoggedCount\Model\LoggedCount',
            'Ecomm\LoggedCount\Model\ResourceModel\LoggedCount'
        );
    }
}
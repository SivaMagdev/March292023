<?php
namespace Ecomm\ExclusivePrice\Model\ResourceModel\ExclusivePrice;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Ecomm\ExclusivePrice\Model\ExclusivePrice', 'Ecomm\ExclusivePrice\Model\ResourceModel\ExclusivePrice');
    }
}
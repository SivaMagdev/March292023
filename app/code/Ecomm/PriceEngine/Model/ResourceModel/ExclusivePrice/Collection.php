<?php
namespace Ecomm\PriceEngine\Model\ResourceModel\ExclusivePrice;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Ecomm\PriceEngine\Model\ExclusivePrice', 'Ecomm\PriceEngine\Model\ResourceModel\ExclusivePrice');
    }
}
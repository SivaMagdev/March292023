<?php
namespace Ecomm\PriceEngine\Model\ResourceModel\Stock;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Ecomm\PriceEngine\Model\Stock', 'Ecomm\PriceEngine\Model\ResourceModel\Stock');
    }
}
<?php
namespace Ecomm\PriceEngine\Model\ResourceModel\RegularPrice;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Ecomm\PriceEngine\Model\RegularPrice', 'Ecomm\PriceEngine\Model\ResourceModel\RegularPrice');
    }
}
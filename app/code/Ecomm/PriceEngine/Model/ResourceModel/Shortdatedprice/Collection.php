<?php
namespace Ecomm\PriceEngine\Model\ResourceModel\Shortdatedprice;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Ecomm\PriceEngine\Model\Shortdatedprice', 'Ecomm\PriceEngine\Model\ResourceModel\Shortdatedprice');
    }
}
<?php
namespace Ecomm\PriceEngine\Model\ResourceModel\ContractPrice;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Ecomm\PriceEngine\Model\ContractPrice', 'Ecomm\PriceEngine\Model\ResourceModel\ContractPrice');
    }
}
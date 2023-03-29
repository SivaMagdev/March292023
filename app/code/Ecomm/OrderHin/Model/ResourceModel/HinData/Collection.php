<?php
namespace Ecomm\OrderHin\Model\ResourceModel\HinData;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Ecomm\OrderHin\Model\HinData', 'Ecomm\OrderHin\Model\ResourceModel\HinData');
    }
}
<?php
namespace Ecomm\ExclusivePrice\Model\ResourceModel\ContractPrice;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Ecomm\ExclusivePrice\Model\ContractPrice', 'Ecomm\ExclusivePrice\Model\ResourceModel\ContractPrice');
    }
     
    protected function _initSelect() {
        parent :: _initSelect();
        $this->addFieldToFilter("deleted",array("neq" => 1));
        return $this;
    }

}
<?php

namespace Ecomm\Sap\Model;

class SalesOrdermodel extends \Magento\Framework\Model\AbstractModel implements
    \Ecomm\Sap\Api\Data\SalesorderdataInterface
{
    const KEY_BATCH_ID = 'BATCH_ID';

    const KEY_SALESORDER = 'SALESORDER';

     public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    public function getBatchId()
    {
        return $this->_getData(self::KEY_BATCH_ID);
    }


    /**
     * Set batch_id
     *
     * @param string $batch_id
     * @return $this
     */
    public function setBatchId($batch_id)
    {
        return $this->setData(self::KEY_BATCH_ID, $batch_id);
    }


    public function getOrders()
    {
        return $this->_getData(self::KEY_SALESORDER);
    }


    /**
     * Set salesorder
     *
     * @param string[] $salesorder
     * @return $this
     */
    public function setOrders($salesorder = array())
    {
        return $this->setData(self::KEY_SALESORDER, $salesorder);
    }
}
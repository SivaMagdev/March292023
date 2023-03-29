<?php

namespace Ecomm\Api\Model;

class ProductAttributesItemsmodel extends \Magento\Framework\Model\AbstractModel implements
    \Ecomm\Api\Api\Data\ProductAttributesItemsdataInterface
{
    const KEY_ITEMS = 'ITEMS';

     public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getItems()
    {
        return $this->_getData(self::KEY_ITEMS);
    }


    /**
     * Set items
     *
     * @param string[] $items
     * @return $this
     */
    public function setItems($items = array())
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }
}
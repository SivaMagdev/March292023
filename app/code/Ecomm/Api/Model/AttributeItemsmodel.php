<?php

namespace Ecomm\Api\Model;

class AttributeItemsmodel extends \Magento\Framework\Model\AbstractModel implements
    \Ecomm\Api\Api\Data\AttributeItemsdataInterface
{
    const KEY_OPTIONS = 'OPTIONS';

     public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getOptions()
    {
        return $this->_getData(self::KEY_OPTIONS);
    }

    /**
     * Set options
     *
     * @param string[] $options
     * @return $this
     */
    public function setOptions($options = array())
    {
        return $this->setData(self::KEY_OPTIONS, $options);
    }
}
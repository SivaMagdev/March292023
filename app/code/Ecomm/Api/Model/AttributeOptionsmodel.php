<?php

namespace Ecomm\Api\Model;

class AttributeOptionsmodel extends \Magento\Framework\Model\AbstractModel implements
    \Ecomm\Api\Api\Data\AttributeOptionsdataInterface
{
    const KEY_LABEL = 'LABEL';

    const KEY_VALUE = 'VALUE';

    const KEY_PRODUCT_COUNT = 'PRODUCT_COUNT';


     public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getLabel()
    {
        return $this->_getData(self::KEY_LABEL);
    }

    /**
     * Set label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->setData(self::KEY_LABEL, $label);
    }

    public function getValue()
    {
        return $this->_getData(self::KEY_VALUE);
    }

    /**
     * Set value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        return $this->setData(self::KEY_VALUE, $value);
    }

    public function getProductCount()
    {
        return $this->_getData(self::KEY_PRODUCT_COUNT);
    }

    /**
     * Set product_count
     *
     * @param string $product_count
     * @return $this
     */
    public function setProductCount($product_count)
    {
        return $this->setData(self::KEY_PRODUCT_COUNT, $product_count);
    }
}
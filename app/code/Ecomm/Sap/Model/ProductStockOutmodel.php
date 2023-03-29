<?php

namespace Ecomm\Sap\Model;

class ProductStockOutmodel extends \Magento\Framework\Model\AbstractModel implements
    \Ecomm\Sap\Api\Data\ProductStockOutdataInterface
{
    const KEY_ARTICLE_CODE = 'ARTICLE_CODE';

    const KEY_DISTRIBUTION_CENTER_CODE = 'DISTRIBUTION_CENTER_CODE';


     public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    public function getArticleCode()
    {
        return $this->_getData(self::KEY_ARTICLE_CODE);
    }


    /**
     * Set article_code
     *
     * @param string $article_code
     * @return $this
     */
    public function setArticleCode($article_code)
    {
        return $this->setData(self::KEY_ARTICLE_CODE, $article_code);
    }


    public function getDistributionCenterCode()
    {
        return $this->_getData(self::KEY_DISTRIBUTION_CENTER_CODE);
    }


    /**
     * Set distribution_center_code
     *
     * @param string $distribution_center_code
     * @return $this
     */
    public function setDistributionCenterCode($distribution_center_code)
    {
        return $this->setData(self::KEY_DISTRIBUTION_CENTER_CODE, $distribution_center_code);
    }


}
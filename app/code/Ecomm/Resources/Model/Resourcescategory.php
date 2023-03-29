<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 *
 * ==============================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * ==============================================================
 * This package designed for Magento COMMUNITY edition
 * PWC does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PWC does not provide extension support in case of
 * incorrect edition usage.
 * ==============================================================
 *
 * @category    PWC
 * @package     Ecomm_Resources
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\Resources\Model;

use Magento\Framework\Model\AbstractModel;
use Ecomm\Resources\Api\Resourcescategory\ResourcescategoryInterface;

/**
 * This class initiates order model
 */
class Resourcescategory extends AbstractModel implements ResourcescategoryInterface {
    /**
     * Define resource model
     */
    protected function _construct() {
        $this->_init ( 'Ecomm\Resources\Model\ResourceModel\Resourcescategory' );
    }

    /**
     * Get cache identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Category
     *
     * @return string
     */
    public function getCategory()
    {
    	return $this->getData(ResourcescategoryInterface::CATEGORY);
    }

    /**
     * Set Category
     *
     * @param $category
     * @return mixed
     */
    public function setCategory($category)
    {
    	return $this->setData(ResourcescategoryInterface::CATEGORY, $category);
    }

    /**
     * Get Status
     *
     * @return bool|int
     */
    public function getStatus()
    {
    	return $this->getData(ResourcescategoryInterface::STATUS);
    }

    /**
     * Set Status
     *
     * @param $status
     * @return DataInterface
     */
    public function setStatus($status)
    {
    	return $this->setData(ResourcescategoryInterface::STATUS, $status);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
    	return $this->getData(ResourcescategoryInterface::CREATED_AT);
    }

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setCreatedAt($createdAt)
    {
    	return $this->setData(ResourcescategoryInterface::CREATED_AT, $createdAt);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
    	return $this->getData(ResourcescategoryInterface::UPDATED_AT);
    }

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return DataInterface
     */
    public function setUpdatedAt($updatedAt)
    {
    	return $this->setData(ResourcescategoryInterface::UPDATED_AT, $updatedAt);
    }
}
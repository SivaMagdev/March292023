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
 * @package     Ecomm_News
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\News\Model;

use Magento\Framework\Model\AbstractModel;
use Ecomm\News\Api\News\NewsInterface;

/**
 * This class initiates order model
 */
class News extends AbstractModel implements NewsInterface {
    /**
     * Define resource model
     */
    protected function _construct() {
        $this->_init ( 'Ecomm\News\Model\ResourceModel\News' );
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
     * Get Title
     *
     * @return string
     */
    public function getTitle()
    {
    	return $this->getData(NewsInterface::TITLE);
    }

    /**
     * Set Title
     *
     * @param $title
     * @return mixed
     */
    public function setTitle($title)
    {
    	return $this->setData(NewsInterface::TITLE, $title);
    }

    /**
     * Get Description
     *
     * @return mixed
     */
    public function getDescription()
    {
    	return $this->getData(NewsInterface::DESCRIPTION);
    }

    /**
     * Set Description
     *
     * @param $description
     * @return mixed
     */
    public function setDescription($description)
    {
    	return $this->setData(NewsInterface::DESCRIPTION, $description);
    }

    /**
     * Get NewsImage
     *
     * @return mixed
     */
    public function getNewsImage()
    {
    	return $this->getData(NewsInterface::REWARDS_IMAGE);
    }

    /**
     * Set NewsImage
     *
     * @param $designation
     * @return mixed
     */
    public function setNewsImage($newsImage)
    {
    	return $this->setData(NewsInterface::REWARDS_IMAGE, $newsImage);
    }

    /**
     * Get Status
     *
     * @return bool|int
     */
    public function getStatus()
    {
    	return $this->getData(NewsInterface::STATUS);
    }

    /**
     * Set Status
     *
     * @param $status
     * @return DataInterface
     */
    public function setStatus($status)
    {
    	return $this->setData(NewsInterface::STATUS, $status);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
    	return $this->getData(NewsInterface::CREATED_AT);
    }

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setCreatedAt($createdAt)
    {
    	return $this->setData(NewsInterface::CREATED_AT, $createdAt);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getPublishedAt()
    {
        return $this->getData(NewsInterface::PUBLISHED_AT);
    }

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setPublishedAt($publishedAt)
    {
        return $this->setData(NewsInterface::PUBLISHED_AT, $publishedAt);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
    	return $this->getData(NewsInterface::UPDATED_AT);
    }

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return DataInterface
     */
    public function setUpdatedAt($updatedAt)
    {
    	return $this->setData(NewsInterface::UPDATED_AT, $updatedAt);
    }
}
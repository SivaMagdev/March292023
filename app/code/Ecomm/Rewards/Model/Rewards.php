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
 * @package     Ecomm_Rewards
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\Rewards\Model;

use Magento\Framework\Model\AbstractModel;
use Ecomm\Rewards\Api\Rewards\RewardsInterface;

/**
 * This class initiates order model
 */
class Rewards extends AbstractModel implements RewardsInterface {
    /**
     * Define resource model
     */
    protected function _construct() {
        $this->_init ( 'Ecomm\Rewards\Model\ResourceModel\Rewards' );
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
    	return $this->getData(RewardsInterface::TITLE);
    }

    /**
     * Set Title
     *
     * @param $title
     * @return mixed
     */
    public function setTitle($title)
    {
    	return $this->setData(RewardsInterface::TITLE, $title);
    }

    /**
     * Get Description
     *
     * @return mixed
     */
    public function getDescription()
    {
    	return $this->getData(RewardsInterface::DESCRIPTION);
    }

    /**
     * Set Description
     *
     * @param $description
     * @return mixed
     */
    public function setDescription($description)
    {
    	return $this->setData(RewardsInterface::DESCRIPTION, $description);
    }

    /**
     * Get RewardsImage
     *
     * @return mixed
     */
    public function getRewardsImage()
    {
    	return $this->getData(RewardsInterface::REWARDS_IMAGE);
    }

    /**
     * Set RewardsImage
     *
     * @param $designation
     * @return mixed
     */
    public function setRewardsImage($rewardsImage)
    {
    	return $this->setData(RewardsInterface::REWARDS_IMAGE, $rewardsImage);
    }

    /**
     * Get Status
     *
     * @return bool|int
     */
    public function getStatus()
    {
    	return $this->getData(RewardsInterface::STATUS);
    }

    /**
     * Set Status
     *
     * @param $status
     * @return DataInterface
     */
    public function setStatus($status)
    {
    	return $this->setData(RewardsInterface::STATUS, $status);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
    	return $this->getData(RewardsInterface::CREATED_AT);
    }

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setCreatedAt($createdAt)
    {
    	return $this->setData(RewardsInterface::CREATED_AT, $createdAt);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
    	return $this->getData(RewardsInterface::UPDATED_AT);
    }

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return DataInterface
     */
    public function setUpdatedAt($updatedAt)
    {
    	return $this->setData(RewardsInterface::UPDATED_AT, $updatedAt);
    }
}
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
use Ecomm\Resources\Api\Resources\ResourcesInterface;

/**
 * This class initiates order model
 */
class Resources extends AbstractModel implements ResourcesInterface {
    /**
     * Define resource model
     */
    protected function _construct() {
        $this->_init ( 'Ecomm\Resources\Model\ResourceModel\Resources' );
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
    	return $this->getData(ResourcesInterface::TITLE);
    }

    /**
     * Set Title
     *
     * @param $title
     * @return mixed
     */
    public function setTitle($title)
    {
    	return $this->setData(ResourcesInterface::TITLE, $title);
    }

    /**
     * Get CategoryId
     *
     * @return string
     */
    public function getCategoryId(){
        return $this->getData(ResourcesInterface::CATEGORY_ID);
    }

    /**
     * Set CategoryId
     *
     * @param $category_id
     * @return mixed
     */
    public function setCategoryId($category_id){
        return $this->setData(ResourcesInterface::CATEGORY_ID, $category_id);
    }

    /**
     * Get Description
     *
     * @return mixed
     */
    public function getDescription()
    {
    	return $this->getData(ResourcesInterface::DESCRIPTION);
    }

    /**
     * Set Description
     *
     * @param $description
     * @return mixed
     */
    public function setDescription($description)
    {
    	return $this->setData(ResourcesInterface::DESCRIPTION, $description);
    }

    /**
     * Get Link
     *
     * @return mixed
     */
    public function getLink()
    {
    	return $this->getData(ResourcesInterface::LINK);
    }

    /**
     * Set Link
     *
     * @param $link
     * @return mixed
     */
    public function setLink($link)
    {
    	return $this->setData(ResourcesInterface::LINK, $link);
    }

    /**
     * Get attachment
     *
     * @return mixed
     */
    public function getAttachment()
    {
    	return $this->getData(ResourcesInterface::ATTACHMENT);
    }

    /**
     * Set attachment
     *
     * @param $designation
     * @return mixed
     */
    public function setAttachment($attachment)
    {
    	return $this->setData(ResourcesInterface::ATTACHMENT, $attachment);
    }

    /**
     * Get Status
     *
     * @return bool|int
     */
    public function getStatus()
    {
    	return $this->getData(ResourcesInterface::STATUS);
    }

    /**
     * Set Status
     *
     * @param $status
     * @return DataInterface
     */
    public function setStatus($status)
    {
    	return $this->setData(ResourcesInterface::STATUS, $status);
    }

    /**
     * Get HideLeavePopup
     *
     * @return bool|int
     */
    public function getHideLeavePopup()
    {
        return $this->getData(ResourcesInterface::HIDE_LEAVE_POPUP);
    }

    /**
     * Set IsLoggedIn
     *
     * @param $hide_leave_popup
     * @return DataInterface
     */
    public function setHideLeavePopup($hide_leave_popup)
    {
        return $this->setData(ResourcesInterface::HIDE_LEAVE_POPUP, $hide_leave_popup);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
    	return $this->getData(ResourcesInterface::CREATED_AT);
    }

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setCreatedAt($createdAt)
    {
    	return $this->setData(ResourcesInterface::CREATED_AT, $createdAt);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
    	return $this->getData(ResourcesInterface::UPDATED_AT);
    }

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return DataInterface
     */
    public function setUpdatedAt($updatedAt)
    {
    	return $this->setData(ResourcesInterface::UPDATED_AT, $updatedAt);
    }
}
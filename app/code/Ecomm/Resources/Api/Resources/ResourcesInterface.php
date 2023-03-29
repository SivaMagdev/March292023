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
namespace Ecomm\Resources\Api\Resources;

interface ResourcesInterface
{
   /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                = 'id';
    const TITLE             = 'title';
    const CATEGORY_ID       = 'category_id';
    const DESCRIPTION       = 'description';
    const LINK              = 'link';
    const ATTACHMENT        = 'attachment';
    const STATUS            = 'status';
    const HIDE_LEAVE_POPUP  = 'hide_leave_popup';
    const CREATED_AT        = 'created_at';
    const UPDATED_AT        = 'updated_at';


    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param $id
     * @return DataInterface
     */
    public function setId($id);

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set Title
     *
     * @param $title
     * @return mixed
     */
    public function setTitle($title);

    /**
     * Get CategoryId
     *
     * @return string
     */
    public function getCategoryId();

    /**
     * Set CategoryId
     *
     * @param $category_id
     * @return mixed
     */
    public function setCategoryId($category_id);

    /**
     * Get Description
     *
     * @return mixed
     */
    public function getDescription();

    /**
     * Set Description
     *
     * @param $description
     * @return mixed
     */
    public function setDescription($description);

    /**
     * Get Link
     *
     * @return mixed
     */
    public function getLink();

    /**
     * Set Link
     *
     * @param $link
     * @return mixed
     */
    public function setLink($link);

    /**
     * Get Attachment
     *
     * @return mixed
     */
    public function getAttachment();

    /**
     * Set Attachment
     *
     * @param $attachment
     * @return mixed
     */
    public function setAttachment($attachment);

    /**
     * Get Status
     *
     * @return bool|int
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param $status
     * @return DataInterface
     */
    public function setStatus($status);

     /**
     * Get Hide Leave Popup
     *
     * @return bool|int
     */
    public function getHideLeavePopup();

    /**
     * Set Is Logged In
     *
     * @param $hide_leave_popup
     * @return DataInterface
     */
    public function setHideLeavePopup($hide_leave_popup);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return DataInterface
     */
    public function setUpdatedAt($updatedAt);
}

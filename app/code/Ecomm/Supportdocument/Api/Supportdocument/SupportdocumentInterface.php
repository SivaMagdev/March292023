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
 * @package     Ecomm_Supportdocument
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\Supportdocument\Api\Supportdocument;

interface SupportdocumentInterface
{
   /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                = 'id';
    const PRODUCT_ID        = 'product_id';
    const LINK_TITLE        = 'link_title';
    const ATTACHMENT        = 'attachment';
    const LINK              = 'link';
    const STATUS            = 'status';
    const IS_LOGGED_IN      = 'is_logged_in';
    const HIDE_LEAVE_POPUP      = 'hide_leave_popup';
    const CREATED_AT        = 'created_at';
    const UPDATED_AT        = 'updated_at';


    /**
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
     * Get ProductId
     *
     * @return string
     */
    public function getProductId();

    /**
     * Set ProductId
     *
     * @param $productId
     * @return mixed
     */
    public function setProductId($productId);

    /**
     * Get LinkTitle
     *
     * @return mixed
     */
    public function getLinkTitle();

    /**
     * Set LinkTitle
     *
     * @param $linkTitle
     * @return mixed
     */
    public function setLinkTitle($linkTitle);

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
     * Get Is Logged In
     *
     * @return bool|int
     */
    public function getIsLoggedIn();

    /**
     * Set Is Logged In
     *
     * @param $is_logged_in
     * @return DataInterface
     */
    public function setIsLoggedIn($is_logged_in);

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

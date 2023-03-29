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
namespace Ecomm\Supportdocument\Model;

use Magento\Framework\Model\AbstractModel;
use Ecomm\Supportdocument\Api\Supportdocument\SupportdocumentInterface;

/**
 * This class initiates order model
 */
class Supportdocument extends AbstractModel implements SupportdocumentInterface {
    /**
     * Define resource model
     */
    protected function _construct() {
        $this->_init ( 'Ecomm\Supportdocument\Model\ResourceModel\Supportdocument' );
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
     * Get Name
     *
     * @return string
     */
    public function getProductId()
    {
    	return $this->getData(SupportdocumentInterface::PRODUCT_ID);
    }

    /**
     * Set Name
     *
     * @param $productId
     * @return mixed
     */
    public function setProductId($productId)
    {
    	return $this->setData(SupportdocumentInterface::PRODUCT_ID, $productId);
    }

    /**
     * Get Description
     *
     * @return mixed
     */
    public function getLinkTitle()
    {
    	return $this->getData(SupportdocumentInterface::LINK_TITLE);
    }

    /**
     * Set Description
     *
     * @param $linkTitle
     * @return mixed
     */
    public function setLinkTitle($linkTitle)
    {
    	return $this->setData(SupportdocumentInterface::LINK_TITLE, $linkTitle);
    }

    /**
     * Get ProfileImage
     *
     * @return mixed
     */
    public function getAttachment()
    {
    	return $this->getData(SupportdocumentInterface::ATTACHMENT);
    }

    /**
     * Set ProfileImage
     *
     * @param $attachment
     * @return mixed
     */
    public function setAttachment($attachment)
    {
    	return $this->setData(SupportdocumentInterface::ATTACHMENT, $attachment);
    }

    /**
     * Get Link
     *
     * @return mixed
     */
    public function getLink()
    {
        return $this->getData(SupportdocumentInterface::LINK);
    }

    /**
     * Set Link
     *
     * @param $link
     * @return mixed
     */
    public function setLink($link)
    {
        return $this->setData(SupportdocumentInterface::LINK, $link);
    }

    /**
     * Get Status
     *
     * @return bool|int
     */
    public function getStatus()
    {
    	return $this->getData(SupportdocumentInterface::STATUS);
    }

    /**
     * Set Status
     *
     * @param $status
     * @return DataInterface
     */
    public function setStatus($status)
    {
    	return $this->setData(SupportdocumentInterface::STATUS, $status);
    }

    /**
     * Get IsLoggedIn
     *
     * @return bool|int
     */
    public function getIsLoggedIn()
    {
        return $this->getData(SupportdocumentInterface::IS_LOGGED_IN);
    }

    /**
     * Set IsLoggedIn
     *
     * @param $is_logged_in
     * @return DataInterface
     */
    public function setIsLoggedIn($is_logged_in)
    {
        return $this->setData(SupportdocumentInterface::IS_LOGGED_IN, $is_logged_in);
    }

    /**
     * Get HideLeavePopup
     *
     * @return bool|int
     */
    public function getHideLeavePopup()
    {
        return $this->getData(SupportdocumentInterface::HIDE_LEAVE_POPUP);
    }

    /**
     * Set IsLoggedIn
     *
     * @param $hide_leave_popup
     * @return DataInterface
     */
    public function setHideLeavePopup($hide_leave_popup)
    {
        return $this->setData(SupportdocumentInterface::HIDE_LEAVE_POPUP, $hide_leave_popup);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
    	return $this->getData(SupportdocumentInterface::CREATED_AT);
    }

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setCreatedAt($createdAt)
    {
    	return $this->setData(SupportdocumentInterface::CREATED_AT, $createdAt);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
    	return $this->getData(SupportdocumentInterface::UPDATED_AT);
    }

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return DataInterface
     */
    public function setUpdatedAt($updatedAt)
    {
    	return $this->setData(SupportdocumentInterface::UPDATED_AT, $updatedAt);
    }
}
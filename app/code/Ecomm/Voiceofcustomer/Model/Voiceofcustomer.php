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
 * @package     Ecomm_Voiceofcustomer
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\Voiceofcustomer\Model;

use Magento\Framework\Model\AbstractModel;
use Ecomm\Voiceofcustomer\Api\Voiceofcustomer\VoiceofcustomerInterface;

/**
 * This class initiates order model
 */
class Voiceofcustomer extends AbstractModel implements VoiceofcustomerInterface {
    /**
     * Define resource model
     */
    protected function _construct() {
        $this->_init ( 'Ecomm\Voiceofcustomer\Model\ResourceModel\Voiceofcustomer' );
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
    public function getName()
    {
    	return $this->getData(VoiceofcustomerInterface::NAME);
    }

    /**
     * Set Name
     *
     * @param $title
     * @return mixed
     */
    public function setName($name)
    {
    	return $this->setData(VoiceofcustomerInterface::NAME, $name);
    }

    /**
     * Get Description
     *
     * @return mixed
     */
    public function getDescription()
    {
    	return $this->getData(VoiceofcustomerInterface::DESCRIPTION);
    }

    /**
     * Set Description
     *
     * @param $description
     * @return mixed
     */
    public function setDescription($description)
    {
    	return $this->setData(VoiceofcustomerInterface::DESCRIPTION, $description);
    }

    /**
     * Get Designation
     *
     * @return mixed
     */
    public function getDesignation()
    {
    	return $this->getData(VoiceofcustomerInterface::DESIGNATION);
    }

    /**
     * Set Designation
     *
     * @param $designation
     * @return mixed
     */
    public function setDesignation($designation)
    {
    	return $this->setData(VoiceofcustomerInterface::DESIGNATION, $designation);
    }

    /**
     * Get ProfileImage
     *
     * @return mixed
     */
    public function getProfileImage()
    {
    	return $this->getData(VoiceofcustomerInterface::PROFILE_IMAGE);
    }

    /**
     * Set ProfileImage
     *
     * @param $designation
     * @return mixed
     */
    public function setProfileImage($profileImage)
    {
    	return $this->setData(VoiceofcustomerInterface::PROFILE_IMAGE, $profileImage);
    }

    /**
     * Get Status
     *
     * @return bool|int
     */
    public function getStatus()
    {
    	return $this->getData(VoiceofcustomerInterface::STATUS);
    }

    /**
     * Set Status
     *
     * @param $status
     * @return DataInterface
     */
    public function setStatus($status)
    {
    	return $this->setData(VoiceofcustomerInterface::STATUS, $status);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
    	return $this->getData(VoiceofcustomerInterface::CREATED_AT);
    }

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setCreatedAt($createdAt)
    {
    	return $this->setData(VoiceofcustomerInterface::CREATED_AT, $createdAt);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
    	return $this->getData(VoiceofcustomerInterface::UPDATED_AT);
    }

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return DataInterface
     */
    public function setUpdatedAt($updatedAt)
    {
    	return $this->setData(VoiceofcustomerInterface::UPDATED_AT, $updatedAt);
    }
}
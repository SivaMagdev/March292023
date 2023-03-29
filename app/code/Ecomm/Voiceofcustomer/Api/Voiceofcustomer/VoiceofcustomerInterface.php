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
namespace Ecomm\Voiceofcustomer\Api\Voiceofcustomer;

interface VoiceofcustomerInterface
{
   /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                = 'id';
    const NAME              = 'name';
    const DESCRIPTION       = 'description';
    const DESIGNATION       = 'designation';
    const PROFILE_IMAGE     = 'profile_image';
    const STATUS            = 'status';
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
     * Get Name
     *
     * @return string
     */
    public function getName();

    /**
     * Set Name
     *
     * @param $name
     * @return mixed
     */
    public function setName($name);

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
     * Get Designation
     *
     * @return mixed
     */
    public function getDesignation();

    /**
     * Set Designation
     *
     * @param $designation
     * @return mixed
     */
    public function setDesignation($designation);

    /**
     * Get ProfileImage
     *
     * @return mixed
     */
    public function getProfileImage();

    /**
     * Set ProfileImage
     *
     * @param $designation
     * @return mixed
     */
    public function setProfileImage($profileImage);

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

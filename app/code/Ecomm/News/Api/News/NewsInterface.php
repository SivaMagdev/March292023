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
namespace Ecomm\News\Api\News;

interface NewsInterface
{
   /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                    = 'id';
    const TITLE                 = 'title';
    const DESCRIPTION           = 'description';
    const REWARDS_IMAGE         = 'news_image';
    const STATUS                = 'status';
    const PUBLISHED_AT          = 'published_at';
    const CREATED_AT            = 'created_at';
    const UPDATED_AT            = 'updated_at';


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
     * Get NewsImage
     *
     * @return mixed
     */
    public function getNewsImage();

    /**
     * Set NewsImage
     *
     * @param $designation
     * @return mixed
     */
    public function setNewsImage($newsImage);

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
     * Get published at
     *
     * @return string
     */
    public function getPublishedAt();

    /**
     * set published at
     *
     * @param $publishedAt
     * @return DataInterface
     */
    public function setPublishedAt($publishedAt);

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

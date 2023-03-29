<?php
/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_VideoList
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\VideoList\Api\VideoList;

interface VideoListInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ID                = 'entity_id';
    public const VIDEO_NAME        = 'video_name';
    public const VIDEO_URL         = 'video_url';
    public const STATUS            = 'video_status';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return DataInterface
     */
    public function setId($id);
    
    /**
     * Get Video Name
     *
     * @return int|null
     */
    public function getVideoName();

    /**
     * Set Video Name
     *
     * @param int $videoName
     * @return DataInterface
     */
    public function setVideoName($videoName);
    
    /**
     * Get Video Url
     *
     * @return int|null
     */
    public function getVideoUrl();

    /**
     * Set Video Url
     *
     * @param int $videoUrl
     * @return DataInterface
     */
    public function setVideoUrl($videoUrl);
    
        /**
         * Get Video Status
         *
         * @return int|null
         */
    public function getVideoStatus();

    /**
     * Set Video Status
     *
     * @param int $videoStatus
     * @return DataInterface
     */
    public function setVideoStatus($videoStatus);
}

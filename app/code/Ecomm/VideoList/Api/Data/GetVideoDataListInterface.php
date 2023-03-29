<?php
/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_VideoList
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\VideoList\Api\Data;

/**
 * @api
 */
interface GetVideoDataListInterface
{
    public const NAME = 'name';
    public const URL  = 'url';

    /**
     * Get Video Name
     *
     * @return string
     */
    public function getName();

      /**
       * Set Video Name
       *
       * @param string $videoName
       * @return $this
       */
    public function setName($videoName);

    /**
     * Get Video Url
     *
     * @return string
     */
    public function getUrl();

      /**
       * Set Video Url
       *
       * @param string $videoUrl
       * @return $this
       */
    public function setUrl($videoUrl);
}

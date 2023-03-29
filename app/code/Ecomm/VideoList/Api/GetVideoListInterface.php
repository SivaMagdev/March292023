<?php
/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_VideoList
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */
namespace Ecomm\VideoList\Api;

/**
 * Company CRUD interface.
 * @api
 */
interface GetVideoListInterface
{
    /**
     * Post Company.
     *
     * @api
     * @param mixed $data
     * @return Ecomm\VideoList\Api\Data\GetVideoDataListInterface
     */
    public function getVideoDetails($data = null);
}

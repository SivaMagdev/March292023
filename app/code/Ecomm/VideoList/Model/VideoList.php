<?php
/**
 * PwC India
 *
 * @category Magento
 * @package BekaertSWSb2B_RequestCertificate
 * @author PwC India
 * @license GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Ecomm\VideoList\Model;

use Magento\Framework\Model\AbstractModel;
use Ecomm\VideoList\Model\ResourceModel\VideoList as ResourceModel;
use Ecomm\VideoList\Api\VideoList\VideoListInterface;

/**
 * Description QuoteExtension Table AbstractModel
 */
class VideoList extends AbstractModel implements VideoListInterface
{
    /**
     * Define resource model
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getVideoName()
    {
        return $this->getData(VideoListInterface::VIDEO_NAME);
    }

    /**
     * Set Name
     *
     * @param int $videoName
     * @return mixed
     */
    public function setVideoName($videoName)
    {
        return $this->setData(VideoListInterface::VIDEO_NAME, $videoName);
    }

    /**
     * Get Url
     *
     * @return string
     */
    public function getVideoUrl()
    {
        return $this->getData(VideoListInterface::VIDEO_URL);
    }

    /**
     * Set Url
     *
     * @param int $videoUrl
     * @return mixed
     */
    public function setVideoUrl($videoUrl)
    {
        return $this->setData(VideoListInterface::VIDEO_URL, $videoUrl);
    }
    /**
     * Get Status
     *
     * @return string
     */
    public function getVideoStatus()
    {
        return $this->getData(VideoListInterface::VIDEO_STATUS);
    }

    /**
     * Set Status
     *
     * @param int $videoStatus
     * @return mixed
     */
    public function setVideoStatus($videoStatus)
    {
        return $this->setData(VideoListInterface::STATUS, $videoStatus);
    }
}

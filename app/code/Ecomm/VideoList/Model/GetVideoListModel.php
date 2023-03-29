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
 
use Ecomm\VideoList\Model\ResourceModel\VideoList\CollectionFactory;
use Ecomm\VideoList\Api\Data\GetVideoDataListInterface;

class GetVideoListModel extends \Magento\Framework\Model\AbstractModel implements GetVideoDataListInterface
{

    /**
     * @inheritDoc
     */
    public function setName($videoName)
    {
        return $this->setData(self::NAME, $videoName);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

     /**
      * @inheritDoc
      */
    public function setUrl($videoUrl)
    {
        return $this->setData(self::URL, $videoUrl);
    }

    /**
     * @inheritDoc
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }
}

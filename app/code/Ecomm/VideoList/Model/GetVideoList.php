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
 
use Ecomm\VideoList\Model\VideoListFactory;
use Ecomm\VideoList\Api\GetVideoListInterface;
use Ecomm\VideoList\Api\Data\GetVideoDataListInterfaceFactory;

class GetVideoList implements GetVideoListInterface
{
    /**
     * @var array
     */
    protected $videoList;
    
    /**
     * @var array
     */
    protected $response;

    /**
     * @param VideoListFactory $videoList
     * @param GetVideoDataListInterfaceFactory $response
     */
    public function __construct(
        VideoListFactory $videoList,
        GetVideoDataListInterfaceFactory $response
    ) {
        $this->videoList = $videoList;
        $this->response = $response;
    }

    /**
     * Return Video List
     *
     * @param array $data
     * @return array
     */
    public function getVideoDetails($data = null)
    {

        $output =[];
        $data = $this->videoList->create();
        $list = $data->getCollection()->addFieldToFilter('video_status', 1);
        foreach ($list as $ds) {
            $re = [];
            $re['name'] = $ds->getVideoName();
            $re['url'] = $ds->getVideoUrl();
            $re['image'] = $ds->getImage();
            array_push($output, $re);
           
        }
        return $output;
    }
}

<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Bannerslider
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Bannerslider\Model\Api;
use Magestore\Bannerslider\Api\BannerListInterface;
use Magento\Store\Model\StoreManagerInterface;
      
class BannerList implements BannerListInterface{


    public function __construct(
        \Magestore\Bannerslider\Model\ResourceModel\Banner\Collection $bannerCollectionFactory,
        StoreManagerInterface $storeManager
    ){
        $this->_bannerCollectionFactory = $bannerCollectionFactory;
        $this->storeManager = $storeManager;
    }
    
     /**
     * @api
     * @param string $sliderId of the param.
     * @return Magestore\Bannerslider\Api\BannerListInterface[]
     */
    public function bannerList($sliderId){
        //echo '<br />bannerList: '.$sliderId;
        $banner_collection = $this->_bannerCollectionFactory->getBannerCollection($sliderId);
        $loadedData = [];
        foreach ($banner_collection as $banner) {
            $_data = $banner->getData();
            $banner->load($banner->getBannerId());

            if (isset($_data['image'])) {
                $_data['banner_image'] = $this->storeManager->getStore()->getBaseUrl(
                                            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                                        ).$banner->getImage();
            }
            $banner->setData($_data);
            $loadedData[] = $banner->getData();
        }
        $banner_list = [
                    "data" => $loadedData,
                    "message"=>"data found",
                    "status" => 1,
                ];
        $data[] =  $banner_list;
        return $data;
    } 

}



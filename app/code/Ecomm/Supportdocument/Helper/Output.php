<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ecomm\Supportdocument\Helper;

use Ecomm\Supportdocument\Model\ResourceModel\Supportdocument\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Output extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->collection = $collectionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * $excludeAttr is optional array of attribute codes to
     * exclude them from additional data array
     *
     * @return array
     */
    public function getAdditionalData($product_id)
    {
        $collection = [];
        $collection = $this->collection->create()
                            ->addFieldToSelect('*')
                            ->addFieldToFilter('product_id', $product_id)
                            ->addFieldToFilter('status', '1');
        return $collection;
    }

    public function getAttachmentUrl($file)
    {
        $attachment_url = '';
        if($file){
            $attachment_url = $this ->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . DIRECTORY_SEPARATOR . 'supportdocument' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'attachment'.DIRECTORY_SEPARATOR.$file;
        }
        return $attachment_url;
    }


}

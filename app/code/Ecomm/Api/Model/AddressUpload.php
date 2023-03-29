<?php

namespace Ecomm\Api\Model;

use Magento\Framework\Model\AbstractModel;
use Ecomm\Api\Api\Data\AddressUploadInterface;

/**
 * This class initiates order model
 */
class AddressUpload extends AbstractModel implements AddressUploadInterface {
    /**
     * Set media gallery content
     *
     * @param $content \Magento\Framework\Api\Data\ImageContentInterface
     * @return $this
     */
    public function setContent($content)
    {
        return $this->setData(AddressUploadInterface::CONTENT, $content);
    }

    /**
     * @return \Magento\Framework\Api\Data\ImageContentInterface|null
     */
    public function getContent()
    {
        return $this->getData(AddressUploadInterface::CONTENT);
    }
}
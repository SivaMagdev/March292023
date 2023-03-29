<?php

namespace Ecomm\Api\Api\Data;

interface AddressUploadInterface
{
    const CONTENT  = 'content';

    /**
     * Set media gallery content
     *
     * @param $content \Magento\Framework\Api\Data\ImageContentInterface
     * @return $this
     */
    public function setContent($content);

    /**
     * @return \Magento\Framework\Api\Data\ImageContentInterface|null
     */
    public function getContent();
}

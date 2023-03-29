<?php

namespace Ecomm\Api\Api;

use Ecomm\Api\Api\Data\AddressUploadInterface;

interface AddressUploadDocumentInterface
{

    /**
     * @param AddressUploadInterface $data
     * @return mixed
     */
    public function save($data);
}

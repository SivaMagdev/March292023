<?php

namespace Ecomm\Api\Model;

use Magento\Store\Model\StoreManagerInterface;
use Ecomm\Api\Api\AddressUploadDocumentInterface;
use Ecomm\Api\Api\Data\AddressUploadInterface;


class AddressUploadDocument implements AddressUploadDocumentInterface
{
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function save($data)
    {
        try {
            /** @var ServicerequestInterface|\Magento\Framework\Model\AbstractModel $data */
            if($data->getContent() && $data->getContent()->getBase64EncodedData() != ''):

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
                $file = $objectManager->create('\Magento\Framework\Filesystem\DriverInterface');
                $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
                $mediaImportPath = $mediaPath.'customer_address/'.$data->getContent()->getName();
                $media = base64_decode($data->getContent()->getBase64EncodedData());
                if (!empty($data->getContent()->getBase64EncodedData())):
                    $imagedata = $file->filePutContents($mediaImportPath,$media);
                endif;
            endif;
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the data: %1',
                $exception->getMessage()
            ));
        }
        return 'success';
    }

}

<?php

namespace Ecomm\Servicerequest\Controller\Index;

use Magento\Framework\Json\Helper\Data as JsonHelper;

class Delete extends \Magento\Framework\App\Action\Action {

    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    public $_storeManager;
    protected $_file;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonHelper $jsonHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_storeManager = $storeManager;
        $this->_file = $file;
    }

    public function execute(){

        $_postData = $this->getRequest()->getPost();

        $message = "";
        $newFileName = "";
        $success = false;

        $mediaRootDir = $this->_mediaDirectory->getAbsolutePath();
        $_fileName = $mediaRootDir .'servicerequest/tmp/attachment/'. $_postData['filename'];
        if ($this->_file->isExists($_fileName))  {
            try{
                $this->attachment->load($_postData['imageID']);
                $this->attachment->delete();

                $this->_file->deleteFile($_fileName);
                $message = "File removed successfully.";
                $success = true;
            } catch (Exception $ex) {
                $message = $e->getMessage();
                $success = false;
            }
        }else{
            $message = "File not found.";
            $success = false;
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData([
                    'message' => $message,
                    'data' => '',
                    'success' => $success
        ]);
    }
}
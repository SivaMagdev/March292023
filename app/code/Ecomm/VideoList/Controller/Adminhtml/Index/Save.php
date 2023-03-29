<?php
/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_VideoList
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\VideoList\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Ecomm\VideoList\Model\VideoListFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Ecomm\VideoList\Model\ImageUploader;
use Magento\Store\Model\StoreManagerInterface;

class Save extends Action
{
    public const VIDEO_PATH = 'video/der_videos/';
    /**
     * @var VideoListFactory
     */
    protected $videoList;

    /**
     * @var ManagerInterface
     */
    protected $message;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Filesystem
     */
    protected $file;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var ImageUploader
     */
    protected $imageUploader;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param VideoListFactory $videoList
     * @param PageFactory $resultPageFactory
     * @param ManagerInterface $message
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        Context $context,
        VideoListFactory $videoList,
        PageFactory $resultPageFactory,
        ManagerInterface $message,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        DirectoryList $directoryList,
        File $file,
        ImageUploader $imageUploader,
        StoreManagerInterface $storeManager
    ) {
        $this->videoList = $videoList;
        $this->resultPageFactory = $resultPageFactory;
        $this->message = $message;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->imageUploader = $imageUploader;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = 0;
        
        $data = $this->getRequest()->getPostValue();
   
        $resultRedirect = $this->resultPageFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');
            $videoName = $this->getRequest()->getParam('video_name');
            $status = $this->getRequest()->getParam('video_status');
            $videoFileName = $this->getRequest()->getParam('video_url');
            $videoFile = $this->getRequest()->getFiles('video_file');
            $imageName = '';
            if (!empty($data['image'])) {
                $imageName = $data['image'][0]['name'];
            } 
            $this->imageUploader->moveFileFromTmp($imageName);

            $thumbnailImageUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'video/thumbnail_image' . '/' . $imageName;


            $this->storeData($id, $videoName, $videoFileName, $status, $thumbnailImageUrl, $imageName);
        }
        return $this->_redirect('*/*/');
    }

    /**
     * Update Video Details List
     *
     * @param int $id
     * @param string $videoName
     * @param string $fileName
     * @param string $status
     * @return array
     */
    private function storeData($id, $videoName, $fileName, $status, $thumbnailImageUrl, $imageName)
    {
        $update = $this->videoList->create();
        if ($id != 0) {
            try {
                $update->setId($id);
                $update->setVideoName($videoName);
                $update->setVideoUrl($fileName);
                $update->setVideoStatus($status);
                $update->setImage($thumbnailImageUrl);
                $update->setImageName($imageName);
                $update->save();
                $this->message->addSuccessMessage('Video Details Updated Successfully');
            } catch (Exception $ex) {
                $this->message->addErrorMessage('Somthing Went Wrong');
                return $this->_redirect('*/*/');
            }
        } else {
            try {
                $update->setVideoName($videoName);
                $update->setVideoUrl($fileName);
                $update->setVideoStatus($status);
                $update->setImage($thumbnailImageUrl);
                $update->setImageName($imageName);
                $update->save();
                $this->message->addSuccessMessage('Video Details Added Successfully');
            } catch (Exception $ex) {
                $this->message->addErrorMessage('Somthing Went Wrong');
                return $this->_redirect('*/*/');
            }
        }
    }
}

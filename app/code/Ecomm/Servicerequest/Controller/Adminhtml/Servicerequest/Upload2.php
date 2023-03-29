<?php


namespace Ecomm\Servicerequest\Controller\Adminhtml\Servicerequest;

use Magento\Framework\Controller\ResultFactory;
use Ecomm\Servicerequest\Model\ImageUploader;

class Upload2 extends \Magento\Backend\App\Action
{
    public $imageUploader;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ecomm\Servicerequest\Model\ImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecomm_Servicerequest::servicerequest');
    }

    public function execute()
    {
        try {
            $result = $this->imageUploader->saveFileToTmpDir('solution_attachment');
            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
<?php

namespace Ecomm\Servicerequest\Controller\Adminhtml\Servicerequest;

use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Message\Manager;
use Magento\Framework\Api\DataObjectHelper;
use Ecomm\Servicerequest\Api\ServicerequestRepositoryInterface;
use Ecomm\Servicerequest\Api\Servicerequest\ServicerequestInterface;
use Ecomm\Servicerequest\Api\Servicerequest\ServicerequestInterfaceFactory;
use Ecomm\Servicerequest\Controller\Adminhtml\Servicerequest;
use Ecomm\BellNotification\Helper\BellNotification;

class Save extends Servicerequest
{
    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $helper;

    protected $emailhelper;

    protected $_requestType;

    protected $customerRepository;

    /**
     * @var Manager
     */
    protected $messageManager;

    /**
     * @var ServicerequestRepositoryInterface
     */
    protected $dataRepository;

    /**
     * @var ServicerequestInterfaceFactory
     */
    protected $dataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    protected $bellNotificationHelper;

    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Notification\Helper\Data $helper,
        \Ecomm\Servicerequest\Helper\Data $emailhelper,
        \Ecomm\Servicerequest\Model\Servicerequest\Source\RequestType $requestType,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        Registry $registry,
        ServicerequestRepositoryInterface $dataRepository,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Manager $messageManager,
        ServicerequestInterfaceFactory $dataFactory,
        BellNotification $bellNotificationHelper,
        DataObjectHelper $dataObjectHelper,
        Context $context
    ) {
        $this->_transportBuilder        = $transportBuilder;
        $this->inlineTranslation        = $inlineTranslation;
        $this->scopeConfig              = $scopeConfig;
        $this->helper                   = $helper;
        $this->emailhelper              = $emailhelper;
        $this->_requestType             = $requestType;
        $this->customerRepository       = $customerRepository;
        $this->messageManager           = $messageManager;
        $this->dataFactory              = $dataFactory;
        $this->dataRepository           = $dataRepository;
        $this->bellNotificationHelper   = $bellNotificationHelper;
        $this->dataObjectHelper         = $dataObjectHelper;
        parent::__construct($registry, $dataRepository, $resultPageFactory, $resultForwardFactory, $context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model = $this->dataRepository->getById($id);
            } else {
                unset($data['id']);
                $model = $this->dataFactory->create();
            }

            try {
                $data = $this->_filterFaqGroupData($data);
                $data = $this->_filterFaqGroupData2($data);

                //echo '<pre>'.print_r($data, true).'</pre>';

                //exit();

                $this->dataObjectHelper->populateWithArray($model, $data, ServicerequestInterface::class);
                $this->dataRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved this data.'));
                $this->_getSession()->setFormServicerequest(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                print_r($e->getMessage());die();
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }

            $this->_getSession()->setFormServicerequest($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Filter faq group data
     *
     * @param array $rawData
     * @return array
     */
    protected function _filterFaqGroupData(array $rawData)
    {
        $data = $rawData;
        if (isset($data['attachment'][0]['name'])) {
            $data['attachment'] = $data['attachment'][0]['name'];
        } else {
            $data['attachment'] = null;
        }

        return $data;
    }

    /**
     * Filter faq group data
     *
     * @param array $rawData
     * @return array
     */
    protected function _filterFaqGroupData2(array $rawData)
    {
        $data = $rawData;
        if (isset($data['solution_attachment'][0]['name'])) {
            $data['solution_attachment'] = $data['solution_attachment'][0]['name'];
        } else {
            $data['solution_attachment'] = null;
        }

        return $data;
    }
}

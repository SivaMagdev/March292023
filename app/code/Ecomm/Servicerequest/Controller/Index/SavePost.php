<?php

namespace Ecomm\Servicerequest\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Api\DataObjectHelper;
use Ecomm\Servicerequest\Api\ServicerequestRepositoryInterface;
use Ecomm\Servicerequest\Api\Servicerequest\ServicerequestInterface;
use Ecomm\Servicerequest\Api\Servicerequest\ServicerequestInterfaceFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Ecomm\BellNotification\Helper\BellNotification;

class SavePost extends Action
{
    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $emailhelper;

    protected $_requestType;

    protected $resultPageFactory;

    protected $customerSession;

    protected $customerRepository;

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

    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Servicerequest\Helper\Data $emailhelper,
        \Ecomm\Servicerequest\Model\Servicerequest\Source\RequestType $requestType,
        Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        PageFactory $resultPageFactory,
        ServicerequestRepositoryInterface $dataRepository,
        ServicerequestInterfaceFactory $dataFactory,
        BellNotification $bellNotificationHelper,
        DataObjectHelper $dataObjectHelper
    )
    {
        $this->_transportBuilder        = $transportBuilder;
        $this->inlineTranslation        = $inlineTranslation;
        $this->scopeConfig              = $scopeConfig;
        $this->emailhelper              = $emailhelper;
        $this->_requestType             = $requestType;
        $this->resultPageFactory        = $resultPageFactory;
        $this->messageManager           = $messageManager;
        $this->customerSession          = $customerSession;
        $this->customerRepository       = $customerRepository;
        $this->dataFactory              = $dataFactory;
        $this->dataRepository           = $dataRepository;
        $this->bellNotificationHelper   = $bellNotificationHelper;
        $this->dataObjectHelper         = $dataObjectHelper;
        parent::__construct($context);
    }

    public function execute()
    {

        if ($this->customerSession->isLoggedIn()) {
               //echo '<pre>'.print_r($data, true).'</pre>'; exit();
            if($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPostValue();
                $data['customer_id'] = $this->customerSession->getId();

                $request_type_id = $data['request_type'];
                $reference_number = $data['reference_number'];
                $model = $this->dataFactory->create();
                $this->dataObjectHelper->populateWithArray($model, $data, ServicerequestInterface::class);
                
                $this->dataRepository->save($model);
                
            } else {
                $this->messageManager->addErrorMessage($e, __("We can\'t submit your request, Please try again."));
            }

            
        } else {
            $this->messageManager->addError( __('Please login to submit the service request.') );
            //echo 'login ';
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;

    }
}
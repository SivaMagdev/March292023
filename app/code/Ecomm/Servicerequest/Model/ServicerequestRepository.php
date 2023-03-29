<?php

namespace Ecomm\Servicerequest\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Ecomm\Servicerequest\Api\ServicerequestRepositoryInterface;
use Ecomm\Servicerequest\Api\Servicerequest\ServicerequestInterface;
use Ecomm\Servicerequest\Api\Servicerequest\ServicerequestInterfaceFactory;
use Ecomm\Servicerequest\Api\Servicerequest\ServicerequestSearchResultsInterfaceFactory;
use Ecomm\Servicerequest\Model\ResourceModel\Servicerequest as ResourceServicerequest;
use Ecomm\Servicerequest\Model\ResourceModel\Servicerequest\CollectionFactory as ServicerequestCollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Ecomm\BellNotification\Helper\BellNotification;
use Ecomm\Servicerequest\Model\Servicerequest\Source\RequestType;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Ecomm\Servicerequest\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Ecomm\Servicerequest\Model\Servicerequest;
use Magento\Customer\Model\Session;
use Ecomm\BellNotification\Helper\PushNotification;

class ServicerequestRepository implements ServicerequestRepositoryInterface
{
    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';
    /**
     * Directory List
     *
     * @var DirectoryList
     */
    protected $directoryList;
    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var ResourceServicerequest
     */
    protected $resource;

    /**
     * @var ServicerequestCollectionFactory
     */
    protected $dataCollectionFactory;

    /**
     * @var ServicerequestSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var ServicerequestInterfaceFactory
     */
    protected $dataInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    public function __construct(
        ResourceServicerequest $resource,
        ServicerequestCollectionFactory $dataCollectionFactory,
        ServicerequestSearchResultsInterfaceFactory $dataSearchResultsInterfaceFactory,
        ServicerequestInterfaceFactory $dataInterfaceFactory,
        StoreManagerInterface $storeManager,
        BellNotification $bellNotificationHelper,
        RequestType $requestType,
        CustomerRepositoryInterface $customerRepository,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        Data $emailhelper,
        ManagerInterface $messageManager,
        Servicerequest $servicerequest,
        Session $customerSession,
        PushNotification $pushNotification,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resource                 = $resource;
        $this->dataCollectionFactory    = $dataCollectionFactory;
        $this->searchResultsFactory     = $dataSearchResultsInterfaceFactory;
        $this->dataInterfaceFactory     = $dataInterfaceFactory;
        $this->dataObjectHelper         = $dataObjectHelper;
        $this->bellNotificationHelper   = $bellNotificationHelper;
        $this->_requestType             = $requestType;
        $this->customerRepository       = $customerRepository;
        $this->inlineTranslation        = $inlineTranslation;
        $this->scopeConfig              = $scopeConfig;
        $this->_transportBuilder        = $transportBuilder;
        $this->emailhelper              = $emailhelper;
        $this->messageManager           = $messageManager;
        $this->servicerequest           = $servicerequest;
        $this->customerSession          = $customerSession;
        $this->pushNotification         = $pushNotification;
        $this->storeManager             = $storeManager;
    }

    /**
     * @param ServicerequestInterface $data
     * @return ServicerequestInterface
     * @throws CouldNotSaveException
     */
    public function save(ServicerequestInterface $data)
    {
        // print_r($data->getData());die();
        try {
            if($data->getContent() && $data->getContent()->getBase64EncodedData() != ''):

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
                $file = $objectManager->create('\Magento\Framework\Filesystem\DriverInterface');
                $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
                $mediaImportPath = $mediaPath.'servicerequest/tmp/attachment/'.$data->getAttachment();
                $media = base64_decode($data->getContent()->getBase64EncodedData());
                if (!empty($data->getContent()->getBase64EncodedData())):
                    $imagedata = $file->filePutContents($mediaImportPath,$media);
                endif;
            endif;

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->create('Ecomm\Servicerequest\Model\Servicerequest');


            $this->servicerequest->setData($data->getData());
            $this->servicerequest->save();

            // $this->resource->save($data);

            $request_id = $this->servicerequest->getId();
            $request_type_id = $data->getRequestType();
            $reference_number = $data->getReferenceNumber();

            if($data->getStatus() == 1){
                $this->bellNotificationHelper->pushToNotification($request_id,$data->getCustomerId(),'Service Request','Request ID# '.$request_id.' Resolved successfully');

                // send mobile notification
                $this->pushNotification->sendPushNotification('service', 'Service Request Updated','Request ID# '.$request_id.' Resolved Successfully',$data->getCustomerId());

                $this->sendNotificationAndMail($request_id, $request_type_id, $reference_number, $data->getCustomerId(), '32', '32');
            }else{
                $this->bellNotificationHelper->pushToNotification($request_id,$data->getCustomerId(),'Service Request','New Request ID# '.$request_id.' Submitted Successfully');

                // send mobile notification
                $this->pushNotification->sendPushNotification('service', 'Service Request Raised','New Request ID# '.$request_id.' Submitted Successfully',$data->getCustomerId());

                $this->sendNotificationAndMail($request_id, $request_type_id, $reference_number, $data->getCustomerId(), '30', '31');
            }

            $this->messageManager->addSuccessMessage(__("Thank you for contacting us with your inquiries. We will revert within 24 hours."));

        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the data: %1',
                $exception->getMessage()
            ));
        }
        return 'success';
    }

    public function sendNotificationAndMail($request_id, $request_type_id, $reference_number, $customerId, $customer_template_id, $admin_template_id){

        $request_types = [];
        $request_types = $this->_requestType->toOptionArray();
        $request_type_list = [];

        foreach($request_types as $request_type){
            $request_type_list[$request_type['value']] = $request_type['label'];
        }

        $customerData = $this->customerRepository->getById($customerId);

        $templateVars = [
            'customer_name' => $customerData->getFirstName().' '.$customerData->getLastName(),
            'request_id' => $request_id,
            'customer_email' => $customerData->getEmail(),
            'title' => $request_type_list[$request_type_id],
            'request_type' => $request_type_list[$request_type_id],
            'reference_number' => $reference_number
        ];

        $this->sendMail($templateVars, $customerData->getEmail(), $customer_template_id, 'customer', $request_type_id);

        /**********************************************/
        $templateVars = [
            'customer_name' => 'Admin',
            'request_id' => $request_id,
            'customer_email' => $customerData->getEmail(),
            'title' => $request_type_list[$request_type_id],
            'request_type' => $request_type_list[$request_type_id],
            'reference_number' => $reference_number
        ];

        $this->sendMail($templateVars, '', $admin_template_id, 'admin', $request_type_id);
    }

    public function sendMail($templateVars, $to_email, $templateId, $type, $request_type){

        if($type == 'admin'){
            $admin_to = $this->emailhelper->getGeneralSupport();

            if($request_type == 1){
                $admin_to = $this->emailhelper->getGeneralSupport();
            } else if($request_type == 2){
                $admin_to = $this->emailhelper->getComplaintsSupport();
            } else if($request_type == 3){
                $admin_to = $this->emailhelper->getInquiriesSupport();
            } else if($request_type == 4){
                $admin_to = $this->emailhelper->getOrderSupport();
            } else if($request_type == 5){
                $admin_to = $this->emailhelper->getRetrunSupport();
            } else if($request_type == 6){
                $admin_to = $this->emailhelper->getDamageSupport();
            } else if($request_type == 7){
                $admin_to = $this->emailhelper->getProfileSupport();
            }

            $to_email = explode(',', $admin_to);
        }

        $this->inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sender = [
            'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $transport =
            $this->_transportBuilder
            ->setTemplateIdentifier($templateId) // Send the ID of Email template which is created in Admin panel
            ->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
            )
            //->setTemplateVars(['data' => $postObject])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            ->addTo($to_email)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    /**
     * Get data record
     *
     * @param $dataId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($dataId)
    {
        if (!isset($this->instances[$dataId])) {
            /** @var \Ecomm\Servicerequest\Api\Servicerequest\ServicerequestInterface|\Magento\Framework\Model\AbstractModel $data */
            $data = $this->dataInterfaceFactory->create();
            $this->resource->load($data, $dataId);
            if (!$data->getId()) {
                throw new NoSuchEntityException(__('Requested data doesn\'t exist'));
            }
            $this->instances[$dataId] = $data;
        }
        return $this->instances[$dataId];
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Ecomm\Servicerequest\Api\Servicerequest\ServicerequestSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Ecomm\Servicerequest\Api\Servicerequest\ServicerequestSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $compositeUserContext = $objectManager->Create('\Magento\Authorization\Model\CompositeUserContext');    
        $customerId = $compositeUserContext->getUserId();   
        // print_r($customerId);die(); 

        /** @var \Ecomm\Servicerequest\Model\ResourceModel\Servicerequest\Collection $collection */
        $collection = $this->dataCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);

        //Add filters from root filter group to the collection
        /** @var FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        } else {
            $field = 'id';
            $collection->addOrder($field, 'ASC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $data = [];
        foreach ($collection as $datum) {
            $dataDataObject = $this->dataInterfaceFactory->create();
            $finalData = $datum->getData();
            if($finalData['attachment']){
                $finalData['attachment_url'] = $this ->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . DIRECTORY_SEPARATOR . 'servicerequest' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'attachment'.DIRECTORY_SEPARATOR.$finalData['attachment'];
            }else{
                $finalData['attachment_url'] = '';
            }

            if($finalData['solution_attachment']){
                $finalData['solution_attachment_url'] = $this ->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . DIRECTORY_SEPARATOR . 'servicerequest' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'attachment'.DIRECTORY_SEPARATOR.$finalData['solution_attachment'];
            }else{
                $finalData['solution_attachment_url'] = '';
            }
            $this->dataObjectHelper->populateWithArray($dataDataObject, $finalData, ServicerequestInterface::class);
            $data[] = $finalData;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($data);
    }

    /**
     * @param ServicerequestInterface $data
     * @return bool
     * @throws CouldNotSaveException
     * @throws StateException
     */
    public function delete(ServicerequestInterface $data)
    {
        /** @var \Ecomm\Servicerequest\Api\Servicerequest\ServicerequestInterface|\Magento\Framework\Model\AbstractModel $data */
        $id = $data->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($data);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to remove data %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * @param $dataId
     * @return bool
     */
    public function deleteById($dataId)
    {
        $data = $this->getById($dataId);
        return $this->delete($data);
    }

    /**
     * Add FilterGroup to the collection
     *
     * @param FilterGroup $filterGroup
     * @param AbstractDb $collection
     * @return void
     */
    private function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        AbstractDb $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $isApplied = false;
            // $customFilter = $this->getCustomFilterForField($filter->getField());
            // if ($customFilter) {
            //     $isApplied = $customFilter->apply($filter, $collection);
            // }

            if (!$isApplied) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $this->getFieldMapping($filter->getField());

                if ($condition === 'fulltext') {
                    // NOTE: This is not a fulltext search, but the best way to search something when
                    // a SearchCriteria with "fulltext" condition is provided over a MySQL table
                    // (see https://github.com/magento-engcom/msi/issues/1221)
                    $condition = 'like';
                    $filter->setValue('%' . $filter->getValue() . '%');
                }

                $conditions[] = [$condition => $filter->getValue()];
            }
        }

        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Return mapped field name
     *
     * @param string $field
     * @return string
     */
    private function getFieldMapping($field)
    {
        return $this->fieldMapping[$field] ?? $field;
    }
}

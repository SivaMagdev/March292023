<?php

namespace Ecomm\Servicerequest\Block;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Ecomm\Servicerequest\Model\ResourceModel\Servicerequest\CollectionFactory;
use Ecomm\Servicerequest\Model\Servicerequest\Source\RequestType;
use Magento\Store\Model\StoreManagerInterface;

class Activerequest extends Template
{

    public $collection;

    protected $customerSession;

    protected $_requestType;

    protected $storeManager;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey $formKey,
        CollectionFactory $collectionFactory,
        RequestType $requestType,
        StoreManagerInterface $storeManager,
        array $data = [])
    {
        $this->customerSession      = $customerSession;
        $this->formKey = $formKey;
        $this->collection = $collectionFactory;
        $this->_requestType = $requestType;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getFilteredRequestType(){

        if($request_type = ($this->getRequest()->getParam('requesttype')) ? $this->getRequest()->getParam('requesttype') : '');

        return $request_type;
    }

    public function getCollection()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        if($request_type = ($this->getRequest()->getParam('requesttype')) ? $this->getRequest()->getParam('requesttype') : '');
        // print_r($this->getRequest()->getParams());
        // die;
        //get values of current page
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 10;
        $collection = $this->collection->create()
         ->addFieldToSelect('*')
         ->addFieldToFilter('customer_id', $customerId)
         ->addFieldToFilter('status', '0')
         ->setOrder('id','DESC');
         if($request_type){
        $collection->addFieldToFilter('request_type', $request_type);
         }
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'Ecomm.Servicerequest.record.pager'
            )->setAvailableLimit(array(10 => 10, 15 => 15, 20 => 20, 25 => 25))->setShowPerPage(true)->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getRequestTypes()
    {
        $requestType = [];
        foreach($this->_requestType->toOptionArray() as $request_type){
            $requestType[$request_type['value']] = $request_type['label'];
        }

        return $requestType;

    }

    public function getRequestType()
    {
    	return $this->_requestType->toOptionArray();
    }

    public function getMediaURL(){
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }

}
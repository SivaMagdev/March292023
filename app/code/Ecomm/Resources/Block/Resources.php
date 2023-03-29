<?php

namespace Ecomm\Resources\Block;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class Resources extends Template
{

    protected $storeManager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteriaInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Ecomm\Resources\Api\ResourcesRepositoryInterface $resourcesRepository,
        \Ecomm\Resources\Api\ResourcescategoryRepositoryInterface $resourcescategoryRepository,
        array $data = [])
    {
        $this->storeManager = $storeManager;
        $this->resourcesRepository = $resourcesRepository;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourcescategoryRepository = $resourcescategoryRepository;
        parent::__construct($context, $data);
    }

    public function getList($category_id){
        $searchCriteria = $this->searchCriteriaBuilder
                                ->addFilter('status',1,'eq')
                                ->addFilter('category_id',$category_id,'eq')
                                ->create();
        return $this->resourcesRepository->getList($searchCriteria);
    }

    public function getCategoryById($id){
        return $this->resourcescategoryRepository->getById($id)->getData();
    }

    public function getCategoryList(){
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('status',1,'eq')->create();
        return $this->resourcescategoryRepository->getList($searchCriteria);
    }

    public function getMediaURL(){
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function clean($string) {
       $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

       return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
}
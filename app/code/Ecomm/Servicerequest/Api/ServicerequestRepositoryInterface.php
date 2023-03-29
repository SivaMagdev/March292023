<?php

namespace Ecomm\Servicerequest\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Ecomm\Servicerequest\Api\Servicerequest\ServicerequestInterface;

interface ServicerequestRepositoryInterface
{

    /**
     * @param ServicerequestInterface $data
     * @return mixed
     */
    public function save(ServicerequestInterface $data);


    /**
     * @param $dataId
     * @return mixed
     */
    public function getById($id);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Ecomm\Servicerequest\Api\Servicerequest\ServicerequestSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param ServicerequestInterface $data
     * @return mixed
     */
    public function delete(ServicerequestInterface $data);

    /**
     * @param $dataId
     * @return mixed
     */
    public function deleteById($id);
}

<?php

/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 *
 * ==============================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * ==============================================================
 * This package designed for Magento COMMUNITY edition
 * PWC does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PWC does not provide extension support in case of
 * incorrect edition usage.
 * ==============================================================
 *
 * @category    PWC
 * @package     Ecomm_Supportdocument
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\Supportdocument\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Ecomm\Supportdocument\Api\SupportdocumentRepositoryInterface;
use Ecomm\Supportdocument\Api\Supportdocument\SupportdocumentInterface;
use Ecomm\Supportdocument\Api\Supportdocument\SupportdocumentInterfaceFactory;
use Ecomm\Supportdocument\Api\Supportdocument\SupportdocumentSearchResultsInterfaceFactory;
use Ecomm\Supportdocument\Model\ResourceModel\Supportdocument as ResourceSupportdocument;
use Ecomm\Supportdocument\Model\ResourceModel\Supportdocument\CollectionFactory as SupportdocumentCollectionFactory;

class SupportdocumentRepository implements SupportdocumentRepositoryInterface
{
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
     * @var ResourceSupportdocument
     */
    protected $resource;

    /**
     * @var SupportdocumentCollectionFactory
     */
    protected $dataCollectionFactory;

    /**
     * @var SupportdocumentSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var SupportdocumentInterfaceFactory
     */
    protected $dataInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    public function __construct(
        ResourceSupportdocument $resource,
        SupportdocumentCollectionFactory $dataCollectionFactory,
        SupportdocumentSearchResultsInterfaceFactory $dataSearchResultsInterfaceFactory,
        SupportdocumentInterfaceFactory $dataInterfaceFactory,
        StoreManagerInterface $storeManager,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resource = $resource;
        $this->dataCollectionFactory = $dataCollectionFactory;
        $this->searchResultsFactory = $dataSearchResultsInterfaceFactory;
        $this->dataInterfaceFactory = $dataInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param SupportdocumentInterface $data
     * @return SupportdocumentInterface
     * @throws CouldNotSaveException
     */
    public function save(SupportdocumentInterface $data)
    {
        try {
            /** @var SupportdocumentInterface|\Magento\Framework\Model\AbstractModel $data */
            $this->resource->save($data);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the data: %1',
                $exception->getMessage()
            ));
        }
        return $data;
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
            /** @var \Ecomm\Supportdocument\Api\Supportdocument\SupportdocumentInterface|\Magento\Framework\Model\AbstractModel $data */
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
     * @return \Ecomm\Supportdocument\Api\Supportdocument\SupportdocumentSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Ecomm\Supportdocument\Api\Supportdocument\SupportdocumentSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Ecomm\Supportdocument\Model\ResourceModel\Supportdocument\Collection $collection */
        $collection = $this->dataCollectionFactory->create();

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
                $finalData['attachment_url'] = $this ->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . DIRECTORY_SEPARATOR . 'supportdocument' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'attachment'.DIRECTORY_SEPARATOR.$finalData['attachment'];
            }else{
                $finalData['attachment_url'] = '';
            }
            $this->dataObjectHelper->populateWithArray($dataDataObject, $finalData, SupportdocumentInterface::class);
            $data[] = $finalData;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($data);
    }

    /**
     * @param SupportdocumentInterface $data
     * @return bool
     * @throws CouldNotSaveException
     * @throws StateException
     */
    public function delete(SupportdocumentInterface $data)
    {
        /** @var \Ecomm\Supportdocument\Api\Supportdocument\SupportdocumentInterface|\Magento\Framework\Model\AbstractModel $data */
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
}

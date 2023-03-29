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
 * @package     Ecomm_Rewards
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\Rewards\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Ecomm\Rewards\Api\RewardsRepositoryInterface;
use Ecomm\Rewards\Api\Rewards\RewardsInterface;
use Ecomm\Rewards\Api\Rewards\RewardsInterfaceFactory;
use Ecomm\Rewards\Api\Rewards\RewardsSearchResultsInterfaceFactory;
use Ecomm\Rewards\Model\ResourceModel\Rewards as ResourceRewards;
use Ecomm\Rewards\Model\ResourceModel\Rewards\CollectionFactory as RewardsCollectionFactory;

class RewardsRepository implements RewardsRepositoryInterface
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
     * @var ResourceRewards
     */
    protected $resource;

    /**
     * @var RewardsCollectionFactory
     */
    protected $dataCollectionFactory;

    /**
     * @var RewardsSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var RewardsInterfaceFactory
     */
    protected $dataInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    public function __construct(
        ResourceRewards $resource,
        RewardsCollectionFactory $dataCollectionFactory,
        RewardsSearchResultsInterfaceFactory $dataSearchResultsInterfaceFactory,
        RewardsInterfaceFactory $dataInterfaceFactory,
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
     * @param RewardsInterface $data
     * @return RewardsInterface
     * @throws CouldNotSaveException
     */
    public function save(RewardsInterface $data)
    {
        try {
            /** @var RewardsInterface|\Magento\Framework\Model\AbstractModel $data */
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
            /** @var \Ecomm\Rewards\Api\Rewards\RewardsInterface|\Magento\Framework\Model\AbstractModel $data */
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
     * @return \Ecomm\Rewards\Api\Rewards\RewardsSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Ecomm\Rewards\Api\Rewards\RewardsSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Ecomm\Rewards\Model\ResourceModel\Rewards\Collection $collection */
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
            if($finalData['rewards_image']){
                $finalData['rewards_image_url'] = $this ->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . DIRECTORY_SEPARATOR . 'rewards' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'rewardsimg'.DIRECTORY_SEPARATOR.$finalData['rewards_image'];
            }else{
                $finalData['rewards_image_url'] = '';
            }
            $this->dataObjectHelper->populateWithArray($dataDataObject, $finalData, RewardsInterface::class);
            $data[] = $finalData;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($data);
    }

    /**
     * @param RewardsInterface $data
     * @return bool
     * @throws CouldNotSaveException
     * @throws StateException
     */
    public function delete(RewardsInterface $data)
    {
        /** @var \Ecomm\Rewards\Api\Rewards\RewardsInterface|\Magento\Framework\Model\AbstractModel $data */
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

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
 * @package     Ecomm_Resources
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\ExclusivePrice\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Ecomm\ExclusivePrice\Api\ContractPriceRepositoryInterface;
use Ecomm\ExclusivePrice\Api\Data\ContractPriceInterface;
use Ecomm\ExclusivePrice\Api\Data\ContractPriceInterfaceFactory;
// use Ecomm\ExclusivePrice\Api\Resources\ResourcesSearchResultsInterfaceFactory;
use Ecomm\ExclusivePrice\Model\ResourceModel\ContractPrice as ContractPrice;
// use Ecomm\Resources\Model\ResourceModel\ContractPrice\CollectionFactory as ContractPriceCollectionFactory;

class ContractPriceRepository implements ContractPriceRepositoryInterface
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
     * @var ResourceResources
     */
    protected $resource;

    /**
     * @var ResourcesCollectionFactory
     */
    // protected $dataCollectionFactory;

    // /**
    //  * @var ResourcesSearchResultsInterfaceFactory
    //  */
    // protected $searchResultsFactory;

    /**
     * @var ResourcesInterfaceFactory
     */
    protected $dataInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    public function __construct(
        ContractPrice $resource,
        // ResourcesCollectionFactory $dataCollectionFactory,
        // ResourcesSearchResultsInterfaceFactory $dataSearchResultsInterfaceFactory,
        ContractPriceInterfaceFactory $dataInterfaceFactory,
        StoreManagerInterface $storeManager,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resource = $resource;
        // $this->dataCollectionFactory = $dataCollectionFactory;
        // $this->searchResultsFactory = $dataSearchResultsInterfaceFactory;
        $this->dataInterfaceFactory = $dataInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param ResourcesInterface $data
     * @return ResourcesInterface
     * @throws CouldNotSaveException
     */
    public function save(ContractPriceInterface $data)
    {
        try {
            /** @var ResourcesInterface|\Magento\Framework\Model\AbstractModel $data */
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
            /** @var \Ecomm\Resources\Api\Resources\ResourcesInterface|\Magento\Framework\Model\AbstractModel $data */
            $data = $this->dataInterfaceFactory->create();
            $this->resource->load($data, $dataId);
            if (!$data->getId()) {
                throw new NoSuchEntityException(__('Requested data doesn\'t exist'));
            }
            $this->instances[$dataId] = $data;
        }
        return $this->instances[$dataId];
    }

    
}

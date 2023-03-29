<?php

/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_GcpIntegration
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\BarcodeUpdater\Block\Adminhtml\Index;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Glob;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

/**
 * Index block class
 */
class Index extends \Magento\Backend\Block\Template
{
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var CollectionFactory
     */
    protected $websiteCollectionFactory;

    /**
     * @var Glob
     */
    protected $glob;

    /**
     * Constructor
     *
     * @param Context $context
     * @param StoreRepositoryInterface $storeRepository
     * @param CollectionFactory $websiteCollectionFactory
     * @param Glob $glob
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreRepositoryInterface $storeRepository,
        CollectionFactory $websiteCollectionFactory,
        Glob $glob,
        array $data = []
    ) {
        $this->storeRepository = $storeRepository;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->glob = $glob;

        parent::__construct($context, $data);
    }

    /**
     * To get store list
     *
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    public function storelist()
    {
        return $this->storeRepository->getList();
    }

    /**
     * To get Website list
     *
     * @return \Magento\Store\Model\ResourceModel\Website\Collection
     */
    public function getWebsiteLists()
    {
        $collection = $this->websiteCollectionFactory->create();
        return $collection;
    }
}

<?php

namespace Ecomm\ExclusivePrice\Controller\Adminhtml;

use Ecomm\ExclusivePrice\Model\ContractPriceFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Ecomm\ExclusivePrice\Api\ContractPriceRepositoryInterface;

abstract class ContractPrice extends Action
{

      /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ACTION_RESOURCE = 'Ecomm_ExclusivePrice::contractprice';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Data repository
     *
     * @var ResourcesRepositoryInterface
     */
    protected $dataRepository;

    /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * exclusiveprice model factory
     *
     * @var \Ecomm\PriceEngine\Model\ExclusivePriceFactory
     */
    protected $_contractpriceFactory;

     /**
     * Result Forward Factory
     *
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
     * @param PageFactory $resultPageFactory
     * @param ExclusivePriceFactory $exclusivepriceFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Ecomm\PriceEngine\Model\ResourceModel\ExclusivePrice\Collection $exclusivepriceCollection
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $_resultPageFactory,
        ContractPriceFactory $contractpriceFactory,
        ForwardFactory $resultForwardFactory,
        ContractPriceRepositoryInterface $dataRepository,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Ecomm\ExclusivePrice\Model\ResourceModel\ContractPrice\Collection $contractpriceCollection,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
      
    ) {
       
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $_resultPageFactory;
        $this->_contractpriceFactory = $contractpriceFactory;
        $this->directoryList = $directoryList;
        $this->dataRepository       = $dataRepository;
        $this->contractpriceCollection = $contractpriceCollection;
        $this->resourceConnection = $resourceConnection;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->groupRepository = $groupRepository;
        parent::__construct($context);
    }

    /**
     * Thana access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecomm_ExclusivePrice::contractprice');
    }
}

<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml;

use Ecomm\PriceEngine\Model\ExclusivePriceFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

abstract class ExclusivePrice extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $_resultForwardFactory;

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
    protected $_exclusivepriceFactory;

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
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        ExclusivePriceFactory $exclusivepriceFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Ecomm\PriceEngine\Model\ResourceModel\ExclusivePrice\Collection $exclusivepriceCollection,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_exclusivepriceFactory = $exclusivepriceFactory;
        $this->directoryList = $directoryList;
        $this->exclusivepriceCollection = $exclusivepriceCollection;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Thana access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecomm_PriceEngine::exclusiveprice');
    }
}

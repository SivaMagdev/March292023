<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml;

use Ecomm\PriceEngine\Model\StockFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

abstract class Stock extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ACTION_RESOURCE = 'Ecomm_PriceEngine::stock';

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
     * stock model factory
     *
     * @var \Ecomm\PriceEngine\Model\StockFactory
     */
    protected $_stockFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
     * @param PageFactory $resultPageFactory
     * @param StockFactory $stockFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Ecomm\PriceEngine\Model\ResourceModel\Stock\Collection $stockCollection
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        StockFactory $stockFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Ecomm\PriceEngine\Model\ResourceModel\Stock\Collection $stockCollection,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_stockFactory = $stockFactory;
        $this->authSession = $authSession;
        $this->directoryList = $directoryList;
        $this->stockCollection = $stockCollection;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Thana access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecomm_PriceEngine::stock');
    }
}

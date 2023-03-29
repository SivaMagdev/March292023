<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml;

use Ecomm\PriceEngine\Model\ShortdatedpriceFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

abstract class Shortdatedprice extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ACTION_RESOURCE = 'Ecomm_PriceEngine::shortdatedprice';

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
     * shortdatedprice model factory
     *
     * @var \Ecomm\PriceEngine\Model\ShortdatedpriceFactory
     */
    protected $_shortdatedpriceFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
     * @param PageFactory $resultPageFactory
     * @param ShortdatedpriceFactory $shortdatedpriceFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Ecomm\PriceEngine\Model\ResourceModel\Shortdatedprice\Collection $shortdatedpriceCollection
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        ShortdatedpriceFactory $shortdatedpriceFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Ecomm\PriceEngine\Model\ResourceModel\Shortdatedprice\Collection $shortdatedpriceCollection,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_shortdatedpriceFactory = $shortdatedpriceFactory;
        $this->directoryList = $directoryList;
        $this->shortdatedpriceCollection = $shortdatedpriceCollection;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Thana access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecomm_PriceEngine::shortdatedprice');
    }
}

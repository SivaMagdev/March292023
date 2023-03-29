<?php

namespace Ecomm\ExclusivePrice\Controller\Adminhtml\ContractPrice;
use Ecomm\ExclusivePrice\Controller\Adminhtml\ContractPrice;
// use Magento\Backend\App\Action;
// use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
// use Ecomm\ExclusivePrice\Controller\RegistryConstants;
// use Magento\Framework\View\Result\PageFactory;
// use Magento\Backend\App\Action\Context;

class Add extends ContractPrice
{

    // const CURRENT_CONTRACTPRICE_ID = 'current_contractprice_id';

    /**
     * Initialize current group and set it in the registry.
     *
     * @return int
     */
       /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    // protected $_resultPageFactory;

    // public function __construct(
    //     PageFactory $resultPageFactory,
    //     Context $context
    //     ) {

    //         $this->_resultPageFactory = $resultPageFactory;
    //         parent::__construct($context);

    //     }
    // protected function _initContractPrice()
    // {
    //     $contract_price_id = $this->getRequest()->getParam('entity_id');

    //     return $contract_price_id;
    // }

    /**
     * Edit or create price.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}

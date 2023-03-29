<?php
namespace Ecomm\Invoice\Controller\Customer;

use  Ecomm\Invoice\Helper\Data;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $helperData;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Data $helperData
    ){
        $this->helperData = $helperData;
        parent::__construct($context);
    }   
  public function execute()
  {
    $resultRedirect = $this->resultRedirectFactory->create();

    if($this->helperData->isLoggedIn())
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
    
    else{
        $resultRedirect->setPath('customer/account/login/');
        return $resultRedirect;
    }

  }
}
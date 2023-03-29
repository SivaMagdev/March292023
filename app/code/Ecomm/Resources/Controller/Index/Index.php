<?php

namespace Ecomm\Resources\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\RedirectFactory;

class Index extends Action
{
    protected $resultPageFactory;


    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RedirectFactory $resultRedirectFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        return $this->resultPageFactory->create();
        die('hello');
    }
}
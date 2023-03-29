<?php
    
    namespace Ecomm\Invoice\Helper;
    
    use Magento\Framework\App\Helper\AbstractHelper;
    use Magento\Framework\App\Helper\Context;
    use Magento\Store\Model\ScopeInterface;
    
    class Data extends AbstractHelper
    {
    protected $context;
    
        public function __construct(
            Context $context,
            \Magento\Customer\Model\Session $customerSession)
        {
            $this->customerSession = $customerSession;
            $this->context = $context;
            parent::__construct($context);
        }
    // Check If Customer Login Or Not
        
        public function isLoggedIn()
        {
            return $this->customerSession->isLoggedIn();
        }
        
}
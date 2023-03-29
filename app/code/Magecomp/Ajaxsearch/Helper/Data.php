<?php
namespace Magecomp\Ajaxsearch\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const AJAXSEARCH_GENERAL_ENABLED = 'ajaxsearch/general/enable';
    const AJAXSEARCH_GENERAL_PRODUCTCOUNT = 'ajaxsearch/general/productcount';

    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getStoreid()
    {
        return $this->_storeManager->getStore()->getId();
    }

    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::AJAXSEARCH_GENERAL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid());
    }

    public function getProductCount()
    {
        if($this->isEnabled()) {
            return $this->scopeConfig->getValue(self::AJAXSEARCH_GENERAL_PRODUCTCOUNT,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreid());
        }
    }

    public function getActiveTemplate()
    {
        if($this->isEnabled())
        {
            return "Magecomp_Ajaxsearch::ajaxsearch.phtml";
        }
        else
        {
            return "Magento_Search::form.mini.phtml";
        }
    }
}
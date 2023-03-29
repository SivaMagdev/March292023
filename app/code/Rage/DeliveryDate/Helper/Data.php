<?php
namespace Rage\DeliveryDate\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->store_manager = $storeManager;
        $this->scope_config = $scopeConfig;
    }
    public function getConfigValue($path)
    {
        $store = $this->getStoreId();
        $config_data = $this->scope_config->getValue($path, ScopeInterface::SCOPE_STORE, $store);
        return $config_data;
    }
    public function getStoreId()
    {
        return $this->store_manager->getStore()->getStoreId();
    }
}

<?php
namespace Ecomm\Api\Model;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class PromotionalConsent
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    )
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }
    /**
     * @inheritdoc
     */
    public function getPromotionalConsent()
    {
        try {
             $promotionalConsent =  $this->scopeConfig->getValue("drl_consent/drl_consent_group/agreement_text",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->storeManager->getStore()->getStoreId());
            $response[] = [
                'status' => true, 
                'promotional_consent_text' => $promotionalConsent
            ];
        } catch (\Exception $e) {
            $response[] = [
                'status' => false, 
                'error' => $e->getMessage()
            ];
            // $this->logger->info($e->getMessage());
        }
        return $response;
   }
}
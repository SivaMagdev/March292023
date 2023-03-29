<?php

namespace Ecomm\FinalPrice\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\Catalog\Api\ScopedProductTierPriceManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Psr\Log\LoggerInterface;

class Product
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var ScopedProductTierPriceManagementInterface
     */
    private $tierPrice;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    private $customerGroupId;

    /**
     * Customer constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param CustomerSession $customerSession
     * @param ScopedProductTierPriceManagementInterface $tierPrice
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        CustomerSession $customerSession,
        ScopedProductTierPriceManagementInterface $tierPrice,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->tierPrice = $tierPrice;
        $this->priceCurrency = $priceCurrency;

        $this->customerGroupId = $this->customerSession->getCustomer()->getGroupId();
    }

    public function afterGetPrice(\Magento\Catalog\Model\Product $product, $result)
    {

    	$customerGroupId = $this->getCustomGroupId();

    	$tierPrices = $product->getData('tier_price');
    	//$this->logger->info('Group price List:'.print_r($tierPrices, true));

    	$group_prices = [];
        $group_price = 0;
        if(is_array($tierPrices)) {
            foreach($tierPrices as $tierPrice){
                $group_prices[$tierPrice['cust_group']] = $tierPrice['price'];
            }
        }

    	if(isset($group_prices[$customerGroupId]) && $group_prices[$customerGroupId] > 0){
            return $group_prices[$customerGroupId];
        } else {
        	return $result;
        }
        //return $result;
    }

    Private function getCustomGroupId(){
        return $this->customerGroupId;
    }
}
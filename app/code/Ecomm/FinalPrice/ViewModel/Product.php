<?php
declare(strict_types=1);

namespace Ecomm\FinalPrice\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\Catalog\Api\ScopedProductTierPriceManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Psr\Log\LoggerInterface;

/**
 * Product view block
 */
class Product implements ArgumentInterface
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

    /**
     * Return customer status
     *
     * @return int
     */
    public function getCustomerGroupPrice($product)
    {
        $groupPrice = ['value' => $product->getPrice(), 'formated' => $this->priceCurrency->format($product->getPrice(), true, 2)];

        $customerGroupId = $this->getCustomGroupId();

        //$this->logger->info('SKU:'.print_r($product->getSku(), true));
       // $this->logger->info('Group Id:'.print_r($customerGroupId, true));

        $tierPrices = [];

        //$result = $this->tierPrice->get([$product->getSku()]);

        $tierPrices = $this->tierPrice->getList($product->getSku(), $customerGroupId);

        if(count($tierPrices)){
            foreach($tierPrices as $tierPrice){
                //$this->logger->info('Group Id:'.print_r($tierPrice->getData(), true));
                //$groupPrice = $tierPrice->getValue();
                $groupPrice = ['value' => $tierPrice->getValue(), 'formated' => $this->priceCurrency->format($tierPrice->getValue(), true, 2)];
            }
        }


        /*if(isset($group_prices[$customerGroupId]) && $group_prices[$customerGroupId] > 0){
            $groupPrice = $group_prices[$customerGroupId];
        }*/
        //$this->logger->info('Group Price:'.print_r($groupPrice, true));

        return $groupPrice;
    }

    Private function getCustomGroupId(){
        return $this->customerGroupId;
    }
}

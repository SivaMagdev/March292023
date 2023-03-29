<?php
namespace Ecomm\StandardShipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class Shipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'standardshipping';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    protected $_cart;

    protected $_productRepository;

    /**
     * Shipping constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array                                                       $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\Request\Http $http,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_cart = $cart;
        $this->_productRepository = $productRepository;
        $this->http = $http;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * get allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @return float
     */
    private function getShippingPrice($request)
    {
        $quoteId = $this->http->getHeader('quote-id');

        if($quoteId){
            $configPrice = $this->getConfigData('price');

            $has_cold_chain_product = 0;

            $quoteFactoryInfo = $this->quoteFactory->create()->addFieldToFilter('quote_id',$quoteId);

            // get quote items array
            $items = $quoteFactoryInfo->getData();

            $subtotal = 0;
            foreach($items as $item) {
                //echo 'ID: '.$item->getProductId().'<br />';
                $_product = $this->_productRepository->getById($item['product_id']);
                if($_product->getColdChain() == 1){
                    $has_cold_chain_product = 1;
                }
                $subtotal += $item['base_row_total'];
            }

            if($subtotal >= 500) {
                $configPrice = 0;
            } else {

                if($has_cold_chain_product == 1){
                    $configPrice = 100;
                } else {
                    $configPrice = 50;
                }
            }
        }else{
            $configPrice = $this->getConfigData('price');

            $has_cold_chain_product = 0;

            if ($request->getAllItems()) {
                foreach ($request->getAllItems() as $item) {


                    if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                        continue;
                    }

                    $_product = $this->_productRepository->getById($item->getProductId());
                    if($_product->getColdChain() == 1){
                        $has_cold_chain_product = 1;
                    }
                }
            }

            // get quote items array
            /*$items = $this->_cart->getQuote()->getAllItems();

            foreach($items as $item) {
                //echo 'ID: '.$item->getProductId().'<br />';
                $_product = $this->_productRepository->getById($item->getProductId());
                if($_product->getColdChain() == 1){
                    $has_cold_chain_product = 1;
                }
            }*/

            //if($this->_cart->getQuote()->getSubtotal() >= 500) {
            if($request->getBaseSubtotalInclTax() >= 500) {
                $configPrice = 0;
            } else {

                if($has_cold_chain_product == 1){
                    $configPrice = 100;
                } else {
                    $configPrice = 50;
                }
            }
        }

        $shippingPrice = $this->getFinalPriceWithHandlingFee($configPrice);

        return $shippingPrice;
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $amount = $this->getShippingPrice($request);

        $method->setPrice($amount);
        $method->setCost($amount);

        $result->append($method);

        return $result;
    }
}

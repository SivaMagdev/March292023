<?php

namespace Ecomm\Api\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Session as CustomerSession;

class CustomPricePlugin
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession          = $customerSession;
    }

    /**
     * @param \Magento\Quote\Model\Quote $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param null|string|\Magento\Framework\DataObject $request
     * @param null|string $processMode
     * @throws \Exception
     * @return array
     */
    public function beforeAddProduct(
        $subject,
        $product,
        $request = null,
        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
    ){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $log = $objectManager->get('\Psr\Log\LoggerInterface');

        $option_value = '';
        if(is_object($request) && (isset($request['options']))){
            $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);
            foreach ($cartCandidates as $candidate) {
                $i = 0;
                foreach ($candidate->getCustomOptions() as $key => $value) {
                    // $log->debug('Options',[$value->getValue()]);
                    $option_values = json_decode($value->getValue(), TRUE);
                    if(isset($option_values['options']) && !empty($option_values['options'])){
                        $option_value = array_values($option_values['options'])[0];
                    }
                }
            }

            $product_load = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
            $customOptions2 = $objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product_load);
            $custom_option_price = 0;
            foreach($customOptions2 as $option) {
                $values = $option->getValues();
                //loop all child options
                foreach($values as $value) {
                    //$log->debug('Options',[$value]);
                    if($option_value == $value->getOptionTypeId()) {
                        $custom_option_price = $value->getDefaultPrice();
                    }
                }
            }

            if($custom_option_price<=0 /*&& isset($request['options'])*/)
                 throw new LocalizedException(__('Unable to add the product '.$product->getSku().' to cart as the product price is Zero.'));

            if($custom_option_price){
                //$log->debug('Custom Price:',[$custom_option_price]);
                $product->setPrice($custom_option_price);
                $request['custom_price'] = $custom_option_price;

                return [$product, $request, $processMode];
            }
        } else {
            /*if ($product->getPrice()<=0) {
                throw new LocalizedException(__('Unable to add the product '.$product->getSku().' to cart as the product price is Zero.'));
            }*/
        }
    }
}

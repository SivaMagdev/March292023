<?php

namespace Ecomm\HidePrice\Pricing\Render;

class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
	/**
     * Wrap with standard required container
     *
     * @param string $html
     * @return string
     */
    protected function wrapResult($html)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $httpContext = $objectManager->get('Magento\Framework\App\Http\Context');
        $isLoggedIn = $httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);

        if($isLoggedIn){
            $customerSession = $objectManager->create('\Magento\Customer\Model\Session');
            $customerRepository = $objectManager->get('\Magento\Customer\Api\CustomerRepositoryInterface');
            $_eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

            if($customerSession->getId()){

                $customerData= $customerRepository->getById($customerSession->getId());

                $attribute = $_eavConfig->getAttribute('customer', 'application_status');
                $options = $attribute->getSource()->getAllOptions();
                $application_statuses = [];
                foreach ($options as $option) {
                    if ($option['value'] > 0) {
                        $application_statuses[$option['value']] = $option['label'];
                    }
                }
                $application_status = 0;
                $approved_id = array_search("Approved",$application_statuses);
                if($customerData->getCustomAttribute('application_status')){
                    $application_status = $customerData->getCustomAttribute('application_status')->getValue();
                }

                if($approved_id == $application_status){

                    return '<div class="price-box ' . $this->getData('css_classes') . '" ' .
                    'data-role="priceBox" ' .
                    'data-product-id="' . $this->getSaleableItem()->getId() . '" ' .
                    'data-price-box="product-id-' . $this->getSaleableItem()->getId() . '"' .
                    '>' . $html . '</div>';

                }
            }

        }
    }
}
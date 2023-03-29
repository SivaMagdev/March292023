<?php

namespace Ecomm\HidePrice\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\Checkout\Controller\Onepage implements HttpGetActionInterface
{

    /**
     * Checkout page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $session = $this->_objectManager->get('\Magento\Customer\Model\Session');
        $customerId = $session->getCustomerId();

        //echo 'customerId: '.$customerId.'-';
        if($customerId) {

            $customerRepository = $this->_objectManager->get('\Magento\Customer\Api\CustomerRepositoryInterface');

            $customer = $customerRepository->getById($customerId);

            $eavAttributeRepository = $this->_objectManager->get('\Magento\Eav\Api\AttributeRepositoryInterface');

            $attributes = $eavAttributeRepository->get(\Magento\Customer\Model\Customer::ENTITY, 'application_status');
            //$options = $attributes->getSource()->getAllOptions(false);

            //echo '<pre>'.print_r($options, true).'</pre>';

            $application_status = $attributes->getSource()->getOptionText($customer->getCustomAttribute("application_status")->getValue());

            if($application_status != 'Approved'){
                $this->messageManager->addErrorMessage('You cannot place an order as your account status is not approved. Please contact customer care to resolve the issue.');
                $resultRedirect = $this->resultRedirectFactory->create()->setPath('/');
                return $resultRedirect;
            }
        }

        /** @var \Magento\Checkout\Helper\Data $checkoutHelper */
        $checkoutHelper = $this->_objectManager->get(\Magento\Checkout\Helper\Data::class);
        if (!$checkoutHelper->canOnepageCheckout()) {
            $this->messageManager->addErrorMessage(__('One-page checkout is turned off.'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        if (!$this->_customerSession->isLoggedIn() && !$checkoutHelper->isAllowedGuestCheckout($quote)) {
            $this->messageManager->addErrorMessage(__('Guest checkout is disabled.'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        // generate session ID only if connection is unsecure according to issues in session_regenerate_id function.
        // @see http://php.net/manual/en/function.session-regenerate-id.php
        if (!$this->isSecureRequest()) {
            $this->_customerSession->regenerateId();
        }
        $this->_objectManager->get(\Magento\Checkout\Model\Session::class)->setCartWasUpdated(false);
        $this->getOnepage()->initCheckout();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Checkout'));
        return $resultPage;
    }

    /**
     * Checks if current request uses SSL and referer also is secure.
     *
     * @return bool
     */
    private function isSecureRequest(): bool
    {
        $request = $this->getRequest();

        $referrer = $request->getHeader('referer');
        $secure = false;

        if ($referrer) {
            $scheme = parse_url($referrer, PHP_URL_SCHEME);
            $secure = $scheme === 'https';
        }

        return $secure && $request->isSecure();
    }

}
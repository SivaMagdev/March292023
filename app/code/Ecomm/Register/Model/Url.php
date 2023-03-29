<?php 

namespace Ecomm\Register\Model;

    class Url extends \Magento\Customer\Model\Url
    {
        public function getRegisterUrl()
    {
        return $this->urlBuilder->getUrl('register-landing-page');
    }

    }
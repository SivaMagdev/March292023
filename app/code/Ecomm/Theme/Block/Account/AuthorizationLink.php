<?php
namespace Ecomm\Theme\Block\Account;
class AuthorizationLink extends \Magento\Customer\Block\Account\AuthorizationLink
{
    public function getLabel()
    {
        return $this->isLoggedIn() ? __('Sign Out') : __('Login');
    }
}
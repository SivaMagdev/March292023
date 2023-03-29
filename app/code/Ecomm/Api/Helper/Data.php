<?php
namespace Ecomm\Api\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Helper for config data.
 */
class Data extends AbstractHelper
{
    /**
     * @return string
     */
    public function getToken1()
    {
        return $this->scopeConfig->getValue('ecomm_api/general/token1');
    }

    /**
     * @return string
     */
    public function getToken2()
    {
        return $this->scopeConfig->getValue('ecomm_api/general/token2');
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->scopeConfig->getValue('ecomm_api/general/username');
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->scopeConfig->getValue('ecomm_api/general/password');
    }

    /**
     * @return string
     */
    public function getAppVersion()
    {
        return $this->scopeConfig->getValue('ecomm_api/general/app_version');
    }

    /**
     * @return string
     */
    public function getTcVersion()
    {
        return $this->scopeConfig->getValue('ecomm_api/general/tc_version');
    }

    /**
     * @return string
     */
    public function getEulaVersion()
    {
        return $this->scopeConfig->getValue('ecomm_api/general/eula_version');
    }
}

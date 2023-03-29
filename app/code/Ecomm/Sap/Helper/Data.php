<?php
namespace Ecomm\Sap\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Helper for config data.
 */
class Data extends AbstractHelper
{
    /**
     * @return string
     */
    public function getDevelopmentUrl()
    {
        return $this->scopeConfig->getValue('ecomm_sap/general/development_url');
    }

    /**
     * @return string
     */
    public function getDevUsername()
    {
        return $this->scopeConfig->getValue('ecomm_sap/general/dev_username');
    }

    /**
     * @return string
     */
    public function getDevPassword()
    {
        return $this->scopeConfig->getValue('ecomm_sap/general/dev_password');
    }

    /**
     * @return string
     */
    public function getLiveUrl()
    {
        return $this->scopeConfig->getValue('ecomm_sap/general/live_url');
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->scopeConfig->getValue('ecomm_sap/general/mode');
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->scopeConfig->getValue('ecomm_sap/general/username');
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->scopeConfig->getValue('ecomm_sap/general/password');
    }
}

<?php
namespace Ecomm\Register\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Helper for config data.
 */
class Data extends AbstractHelper
{
    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->scopeConfig->getValue('ecomm_medpro/general/client_id');
    }

    /**
     * @return string
     */
    public function geClientSecret()
    {
        return $this->scopeConfig->getValue('ecomm_medpro/general/client_secret');
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->scopeConfig->getValue('ecomm_medpro/general/status');
    }
}

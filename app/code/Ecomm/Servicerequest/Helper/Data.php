<?php
namespace Ecomm\Servicerequest\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Helper for config data.
 */
class Data extends AbstractHelper
{
    /**
     * @return string
     */
    public function getGeneralSupport()
    {
        return $this->scopeConfig->getValue('ecomm_servicerequest/general/general_support_email');
    }

    /**
     * @return string
     */
    public function getComplaintsSupport()
    {
        return $this->scopeConfig->getValue('ecomm_servicerequest/general/complaints_support_email');
    }

    /**
     * @return string
     */
    public function getInquiriesSupport()
    {
        return $this->scopeConfig->getValue('ecomm_servicerequest/general/inquiries_support_email');
    }

    /**
     * @return string
     */
    public function getOrderSupport()
    {
        return $this->scopeConfig->getValue('ecomm_servicerequest/general/order_support_email');
    }

    /**
     * @return string
     */
    public function getRetrunSupport()
    {
        return $this->scopeConfig->getValue('ecomm_servicerequest/general/retrun_support_email');
    }

    /**
     * @return string
     */
    public function getDamageSupport()
    {
        return $this->scopeConfig->getValue('ecomm_servicerequest/general/damage_support_email');
    }

    /**
     * @return string
     */
    public function getProfileSupport()
    {
        return $this->scopeConfig->getValue('ecomm_servicerequest/general/profile_support_email');
    }
}

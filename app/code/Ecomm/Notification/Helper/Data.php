<?php
namespace Ecomm\Notification\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Helper for config data.
 */
class Data extends AbstractHelper
{
    /**
     * @return string
     */
    public function emailPriceExpiryTemplate() : string
    {
        return (string) $this->scopeConfig->getValue('ecomm_notification/general/price_expiry_email_template');
    }

    /**
     * @return string
     */
    public function emailLicenceExpiryTemplate() : string
    {
        return (string) $this->scopeConfig->getValue('ecomm_notification/general/licence_expiry_email_template');
    }

    /**
     * @return string
     */
    public function emailLicenceExpiryCustomerTemplate() : string
    {
        return (string) $this->scopeConfig->getValue('ecomm_notification/general/licence_expiry_customer_email_template');
    }

    /**
     * @return string
     */
    public function emailOrderProcessingFailedTemplate() : string
    {
        return (string) $this->scopeConfig->getValue('ecomm_notification/general/order_processing_failed_email_template');
    }

    /**
     * @return string
     */
    public function emailAckFailedTemplate() : string
    {
        return (string) $this->scopeConfig->getValue('ecomm_notification/general/ack_failed_email_template');
    }

    /**
     * @return string
     */
    public function emailAckDuplicateTemplate() : string
    {
        return (string) $this->scopeConfig->getValue('ecomm_notification/general/ack_duplicate_email_template');
    }

    /**
     * @return string
     */
    public function getToEmails()
    {
        return $this->scopeConfig->getValue('ecomm_notification/general/to_emails');
    }

    /**
     * @return string
     */
    public function getSlThreshold()
    {
        return $this->scopeConfig->getValue('ecomm_notification/general/sl_threshold');
    }

    /**
     * @return string
     */
    public function getDeaThreshold()
    {
        return $this->scopeConfig->getValue('ecomm_notification/general/dea_threshold');
    }
}

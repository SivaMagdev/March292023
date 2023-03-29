<?php
 
namespace Ecomm\AjaxNewsletter\Model;
 
class Subscriber extends \Magento\Newsletter\Model\Subscriber
{
    /**
     * Sends out confirmation email
     *
     * @return $this
     */
    public function sendConfirmationRequestEmail($templateVar=[])
    {
        $vars = [
            'store' => $this->_storeManager->getStore($this->getStoreId()),
            'subscriber_data' => [
                'confirmation_link' => $this->getConfirmationLink(),
            ],
        ];

        $vars = array_merge($vars,$templateVar);
        $this->sendEmail(self::XML_PATH_CONFIRM_EMAIL_TEMPLATE, self::XML_PATH_CONFIRM_EMAIL_IDENTITY, $vars);

        return $this;
    }

    /**
     * Sends out confirmation success email
     *
     * @return $this
     */
    public function sendConfirmationSuccessEmail()
    {
       
        //$this->sendEmail(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE, self::XML_PATH_SUCCESS_EMAIL_IDENTITY,$templateVar);

        return $this;
    }

    /**
     * Sends out unsubscription email
     *
     * @return $this
     */
    public function sendUnsubscriptionEmail($templateVar=[])
    {
        //$this->sendEmail(self::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE, self::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY,$templateVar);

        return $this;
    }

    /**
     * Sends out confirmation success email
     *
     * @return $this
     */
    public function sendConfirmationSuccessEmailCustom($templateVar=[])
    {
        $this->sendEmail(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE, self::XML_PATH_SUCCESS_EMAIL_IDENTITY,$templateVar);

        return $this;
    }

    
}
?>
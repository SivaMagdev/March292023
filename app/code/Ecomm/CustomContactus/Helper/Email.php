<?php
namespace Ecomm\CustomContactus\Helper;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;

class Email extends AbstractHelper
{

    const XML_PATH_EMAIL_ADMIN_CONFIRM = 'contactus/adminemail/contact_admin_confirmation';
    const XML_PATH_EMAIL_ADMIN_CONFIRM_TEMPLATE = 'contactus/adminemail/contact_admin_confirmation_template';

    const XML_PATH_EMAIL_CUSTOMER_CONFIRM = 'contactus/email/contact_customer_confirmation';
    const XML_PATH_EMAIL_CUSTOMER_CONFIRM_TEMPLATE = 'contactus/email/contact_customer_confirmation_template';


    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        LoggerInterface $logger
    )
    {
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->logger = $logger;
        parent::__construct($context);
    }


    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerEmailConfirmation()
    {
        $storeId = $this->getStoreId();
        return $this->getConfigValue(self::XML_PATH_EMAIL_CUSTOMER_CONFIRM, $storeId);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerEmailConfirmationTemplate()
    {
        $storeId = $this->getStoreId();       
        return $this->getConfigValue(self::XML_PATH_EMAIL_CUSTOMER_CONFIRM_TEMPLATE, $storeId);
    }


    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAdminEmailConfirmation()
    {
        $storeId = $this->getStoreId();
        return $this->getConfigValue(self::XML_PATH_EMAIL_ADMIN_CONFIRM, $storeId);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAdminEmailConfirmationTemplate()
    {
        $storeId = $this->getStoreId();
        return $this->getConfigValue(self::XML_PATH_EMAIL_ADMIN_CONFIRM_TEMPLATE, $storeId);
    }

    

    /**
     * Send Mail
     *
     * @return $this
     *
     * @throws LocalizedException
     * @throws MailException
     */
    public function sendMail($templateId,$toemail,$templateVars)
    {
        
        $this->inlineTranslation->suspend();
        $storeId = $this->getStoreId();
        // set from email
        $sender['email'] = $this->scopeConfig->getValue('trans_email/ident_support/email',ScopeInterface::SCOPE_STORE,$this->getStoreId());

        $sender['name'] = $this->scopeConfig->getValue('trans_email/ident_support/name',ScopeInterface::SCOPE_STORE,$this->getStoreId());


        $transport = $this->transportBuilder
        ->setTemplateIdentifier($templateId)
        ->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
        )->setTemplateVars(
            $templateVars
        )->setFrom($sender)
        ->addTo($toemail)
        ->getTransport();

        try {
            $transport->sendMessage();
        } catch (\Exception $exception) {
          $this->logger->critical($exception->getMessage());
        }
    
        $this->inlineTranslation->resume();

        return $this;
    }

    /*
     * get Current store id
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /*
     * get Current store Info
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    public function getConfigValue($xmlPath, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $xmlPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return string
     */
    public function emailAdmin() : string
    {
        return (string) $this->getModuleConfig('adminemail/admin_email');
    }

    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue(
            'contactus/' . $path,
            ScopeInterface::SCOPE_STORE
        );
    }


     /**
     * @return string
     */
    public function getToAdminEmails()
    {
        return $this->scopeConfig->getValue('ecomm_notification/general/to_emails');
    }

}
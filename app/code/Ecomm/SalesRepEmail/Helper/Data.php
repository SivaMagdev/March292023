<?php
namespace Ecomm\SalesRepEmail\Helper;

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
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;

class Data extends AbstractHelper
{
    const EMAIL_TEMPLATE = 'ecomm_salesrepemail/order/template';
    const EMAIL_SHIPMENT_TEMPLATE = 'ecomm_salesrepemail/shipment/template';

    const EMAIL_SERVICE_ENABLE = 'ecomm_salesrepemail/order/enabled';

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
     * Send Mail
     *
     * @return $this
     *
     * @throws LocalizedException
     * @throws MailException
     */
    public function sendMail($vars)
    {
        
        $email = $vars['contact_person_email']; //set receiver mail

        $this->inlineTranslation->suspend();
        $storeId = $this->getStoreId();

        /* email template */
       
        $template = $this->scopeConfig->getValue(
            self::EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
   

        $sender = $this->scopeConfig->getValue(
            'ecomm_salesrepemail/order/identity',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $bcc = $this->scopeConfig->getValue(
            'ecomm_salesrepemail/order/copy_to',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        if(isset($bcc)){
            $bcc = explode (",", $bcc); 
            } 

        $transport = $this->transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area' => Area::AREA_FRONTEND,
                'store' => $this->getStoreId()
            ]
        )->setTemplateVars(
            $vars
        )->setFromByScope(
            $sender
        )->addTo(
            $email
        )->getTransport();
        if(isset($bcc)){
            $this->transportBuilder->addBcc($bcc);
        }
        try {
            $transport->sendMessage();
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        $this->inlineTranslation->resume();

        return $this;
    }

    public function sendShipmentMail($vars)
    {
        
        $email = $vars['contact_person_email']; //set receiver mail

        $this->inlineTranslation->suspend();
        $storeId = $this->getStoreId();

        /* email template */
       
        $template = $this->scopeConfig->getValue(
            self::EMAIL_SHIPMENT_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
   

        $sender = $this->scopeConfig->getValue(
            'ecomm_salesrepemail/shipment/identity',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $bcc = $this->scopeConfig->getValue(
            'ecomm_salesrepemail/shipment/copy_to',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        if(isset($bcc)){
        $bcc = explode (",", $bcc); 
        } 

        $transport = $this->transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area' => Area::AREA_FRONTEND,
                'store' => $this->getStoreId()
            ]
        )->setTemplateVars(
            $vars
        )->setFromByScope(
            $sender
        )->addTo(
            $email
        )->getTransport();
        if(isset($bcc)){
        $this->transportBuilder->addBcc($bcc);
        }
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
}
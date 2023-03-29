<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\ArticleDeltaInterface;

class ArticleDelta implements ArticleDeltaInterface {

    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $_helper;

    public $_request;

    protected $_loggerFactory;

    protected $_logger;

	public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Ecomm\Sap\Model\LoggerModel $loggerFactory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_transportBuilder        = $transportBuilder;
        $this->inlineTranslation        = $inlineTranslation;
        $this->scopeConfig              = $scopeConfig;
        $this->_helper                  = $helper;
        $this->_request         		= $request;
        $this->_loggerFactory  			= $loggerFactory;
        $this->_logger          		= $logger;
    }

	/**
	 * {@inheritdoc}
	 */
	public function getArticleDelta()
	{
		$returnData = [];

        $this->_loggerFactory->createLog('ArticleDeltaReq: '.$this->_request->getContent());

        $this->inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sender = [
            'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];

        $templateVars = [];

        $this->_logger->critical('ArticleDeltaReq', ['data' => $this->_request->getContent()]);

        $requestData = json_decode($this->_request->getContent());

        if($requestData){

            /*try {

                $templateVars = ['response' => json_encode($requestData)];

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                //$to_emails = explode(',', $this->_helper->getToEmails());
                $to_emails[] = 'testuser2.pwc@gmail.com';
                //$to_emails[] = 'mohamed.a.ibrahim@pwc.com';
                $transport =
                    $this->_transportBuilder
                    ->setTemplateIdentifier('19') // Send the ID of Email template which is created in Admin panel
                    ->setTemplateOptions(
                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                    )
                    //->setTemplateVars(['data' => $postObject])
                    ->setTemplateVars($templateVars)
                    ->setFrom($sender)
                    //->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
                    ->addTo($to_emails)
                    ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
                //echo 'email sent';
            } catch (\Exception $e) {
                \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug($e->getMessage());
            }*/

        }

        return $returnData;
	}

    public function sendErrorNotification($params)
    {
        $this->inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sender = [
            'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];

        $templateVars = [];
        $templateVars = $params;

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $to_emails = explode(',', $this->_helper->getToEmails());
        //$to_emails[] = 'maideen.i@gmail.com';
        //$to_emails[] = 'mohamed.a.ibrahim@pwc.com';
        $transport =
            $this->_transportBuilder
            ->setTemplateIdentifier('28') // Send the ID of Email template which is created in Admin panel
            ->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
            )
            //->setTemplateVars(['data' => $postObject])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            //->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
            ->addTo($to_emails)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
        //echo 'email sent';
    }

    public function sendAdminNotification($params)
    {
        $this->inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sender = [
            'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
            'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
        ];

        $templateVars = [];
        $templateVars = $params;

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $to_emails = explode(',', $this->_helper->getToEmails());
        //$to_emails[] = 'maideen.i@gmail.com';
        //$to_emails[] = 'mohamed.a.ibrahim@pwc.com';
        $transport =
            $this->_transportBuilder
            ->setTemplateIdentifier('26') // Send the ID of Email template which is created in Admin panel
            ->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
            )
            //->setTemplateVars(['data' => $postObject])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            //->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
            ->addTo($to_emails)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
        //echo 'email sent';
    }
}
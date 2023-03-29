<?php

namespace Ecomm\Register\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Contact\Model\ConfigInterface;

class CustomerAddressSaveAfter implements ObserverInterface
{

	const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

	const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

	protected $_transportBuilder;

	protected $inlineTranslation;

	protected $scopeConfig;

	protected $storeManager;

 	protected $_escaper;

 	protected $_request;

 	protected $_customerSession;

 	protected $_helper;

    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Escaper $escaper,
		\Ecomm\Notification\Helper\Data $helper,
		\Magento\Framework\App\RequestInterface $request
    ) {
		$this->_transportBuilder = $transportBuilder;
		$this->inlineTranslation = $inlineTranslation;
		$this->scopeConfig = $scopeConfig;
		$this->storeManager = $storeManager;
		$this->_customerSession = $customerSession;
		$this->_escaper = $escaper;
		$this->_request = $request;
		$this->_helper = $helper;
	}

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

		$address_id = $observer->getCustomerAddress()->getEntityId();
		//echo '<pre>'.print_r($this->_request->getPost(), true).'</pre>';

		//$postData = $this->_request->getPost();

		$notify = $this->_request->getPost("notify");

		//echo $notify;

		//exit();

		//echo 'First Name:'.$this->_customerSession->getCustomer()->getName();
		//echo 'Email:'.$this->_customerSession->getCustomer()->getEmail();

		//echo 'address_id'.$address_id;
		//exit();

		if($notify >= 1) {

			try
			{
				$error = false;

				$this->inlineTranslation->suspend();

				// Send Email to Admin
				$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
				$sender = [
					'name' => $this->_escaper->escapeHtml($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope)),
					'email' => $this->_escaper->escapeHtml($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope)),
				];
				$postObject = new \Magento\Framework\DataObject();
				$postObject->setData($sender);
				$to_emails = explode(',', $this->_helper->getToEmails());

				$templateVars =[];

				$templateVars = ['customer_email' => $this->_customerSession->getCustomer()->getEmail()];

				$transport =
					$this->_transportBuilder
					->setTemplateIdentifier('11') // Send the ID of Email template which is created in Admin panel
					->setTemplateOptions(
						['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
						'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
					)
					//->setTemplateVars(['data' => $postObject])
					->setTemplateVars($templateVars)
					->setFrom($sender)
					->addTo($to_emails)
					->getTransport();
				$transport->sendMessage();

				$this->inlineTranslation->resume();

				$this->inlineTranslation->suspend();

				// Send Email to User
				$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

				$templateVars =[];

				$templateVars = ['customer_name' => $this->_customerSession->getCustomer()->getName()];

				$sender = [
					'name' => $this->_escaper->escapeHtml($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope)),
					'email' => $this->_escaper->escapeHtml($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope)),
				];
				$postObject = new \Magento\Framework\DataObject();
				$postObject->setData($sender);

				$transport =
					$this->_transportBuilder
					->setTemplateIdentifier('10') // Send the ID of Email template which is created in Admin panel
					->setTemplateOptions(
						['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
						'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
					)
					//->setTemplateVars(['data' => $postObject])
					->setTemplateVars($templateVars)
					->setFrom($sender)
					->addTo($this->_customerSession->getCustomer()->getEmail())
					->getTransport();
				$transport->sendMessage();


				$this->inlineTranslation->resume();

			}
			catch (\Exception $e)
			{
				\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug($e->getMessage());
				//echo $e->getMessage();
				//exit();
			}
		}
	}
}
<?php

namespace Ecomm\Register\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendMailToAdmin implements ObserverInterface
{

	const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

	const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

	protected $_transportBuilder;
	protected $inlineTranslation;
	protected $scopeConfig;
	protected $storeManager;
 	protected $_escaper;
 	protected $_helper;

    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Escaper $escaper,
		\Ecomm\Notification\Helper\Data $helper
    ) {
		$this->_transportBuilder = $transportBuilder;
		$this->inlineTranslation = $inlineTranslation;
		$this->scopeConfig = $scopeConfig;
		$this->storeManager = $storeManager;
		$this->_escaper = $escaper;
		$this->_helper = $helper;
	}

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

		$customer = $observer->getData('customer');
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

		//echo $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope); exit();

		//echo $to_emails.'<br />';
		//echo $cc_emails.'<br />';

		//exit();

		$this->inlineTranslation->suspend();
		try {
			$error = false;

			/*$sender = [
				'name' => $this->_escaper->escapeHtml($customer->getFirstName()),
				'email' => $this->_escaper->escapeHtml($customer->getEmail()),
			];*/
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			$sender = [
				'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
				'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
			];

			$organization_name = '';
			$organization_email = '';

			if (!empty($customer->getCustomAttribute("organization_name"))) {
	            $organization_name = $customer->getCustomAttribute("organization_name")->getValue();
	        }

	        if (!empty($customer->getCustomAttribute("organization_email"))) {
	            $organization_email = $customer->getCustomAttribute("organization_email")->getValue();
	        }

			$templateVars = [
				'customer_name' => $customer->getFirstName(),
				'customer_email' => $customer->getEmail(),
				'customer_company_name' => $organization_name,
				'customer_company_email' => $organization_email
			];
			$postObject = new \Magento\Framework\DataObject();
			$postObject->setData($sender);
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			$to_emails = explode(',', $this->_helper->getToEmails());
			$transport =
				$this->_transportBuilder
				->setTemplateIdentifier('2') // Send the ID of Email template which is created in Admin panel
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

		} catch (\Exception $e) {
			\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug($e->getMessage());
		}
	}
}
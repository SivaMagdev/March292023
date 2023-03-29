<?php

namespace Ecomm\Register\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;
use Magento\Contact\Model\ConfigInterface;
use Ecomm\BellNotification\Helper\BellNotification;
use Ecomm\BellNotification\Helper\PushNotification;
use \Psr\Log\LoggerInterface;

class CustomerAddressSaveAfter implements ObserverInterface
{

	const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

	const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

	protected $_transportBuilder;

	protected $inlineTranslation;

	protected $scopeConfig;

	protected $storeManager;

	protected $bellNotificationHelper;

	protected $pushNotification;

 	protected $_escaper;

    protected $logger;

    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        BellNotification $bellNotificationHelper,
        PushNotification $pushNotification,
		\Magento\Framework\Escaper $escaper,
        LoggerInterface $logger
    ) {
		$this->_transportBuilder = $transportBuilder;
		$this->inlineTranslation = $inlineTranslation;
		$this->scopeConfig = $scopeConfig;
		$this->storeManager = $storeManager;
        $this->bellNotificationHelper   = $bellNotificationHelper;
        $this->pushNotification         = $pushNotification;
		$this->_escaper = $escaper;
        $this->logger 				= $logger;
	}

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

		$address_id = $observer->getCustomerAddress()->getEntityId();

		//$this->logger->log('ERROR','Admin address save:',[$address_id]);

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$_addressFactory = $objectManager->get("Magento\Customer\Model\AddressFactory");
		$customerRepository = $objectManager->get("\Magento\Customer\Api\CustomerRepositoryInterface");

		 $_eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

        $attribute = $_eavConfig->getAttribute('customer_address', 'address_status');
        $options = $attribute->getSource()->getAllOptions();
        $address_status_value = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $address_status_value[$option['value']] = $option['label'];
            }
        }

		$addressData = $_addressFactory->create()->load($address_id);

		//echo '<pre>'.print_r($addressData->getData(), true).'</pre>';

		$customer_id = $addressData->getParentId();

		$address_status = $addressData->getCustomAttribute("address_status")->getValue();

		$customer_repository = $customerRepository->getById($customer_id);

		$customer_email = $customer_repository->getEmail();
		$customer_name = $customer_repository->getFirstname();

		//echo 'address_id: '.$address_id;
		//echo 'customer_id: '.$customer_id;
		//echo 'customer_email: '.$customer_email;
		//echo 'address_status: '.$address_status_value[$address_status];

		//exit();



		if($address_status_value[$address_status] == 'Approved'){

			$this->bellNotificationHelper->pushToNotification($address_id, $customer_id, 'Address Status Updated', 'Your New Address has been approved');

            // send mobile notification
            $this->pushNotification->sendPushNotification('address', 'Address Status Updated', 'Your New Address has been approved', $customer_id);

			//echo 'address_status: '.$address_status_value[$address_status];

			$this->inlineTranslation->suspend();
			try {
				$error = false;

				// Send Email to User
				$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
				$sender = [
					'name' => $this->_escaper->escapeHtml($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope)),
					'email' => $this->_escaper->escapeHtml($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope)),
				];
				$postObject = new \Magento\Framework\DataObject();
				$postObject->setData($sender);
				$templateVars = ['customer_name' => $customer_name];
				$transport =
					$this->_transportBuilder
					->setTemplateIdentifier('12') // Send the ID of Email template which is created in Admin panel
					->setTemplateOptions(
						['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
						'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
					)
					->setTemplateVars($templateVars)
					->setFrom($sender)
					->addTo($customer_email)
					//->addCc('maideen.i@gmail.com')
					->getTransport();
				$transport->sendMessage();

				$this->inlineTranslation->resume();

			}
			catch (\Exception $e)
			{
				\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug($e->getMessage());

				//echo $e->getMessage();
			}

		}
		//exit();

		/**/
	}
}
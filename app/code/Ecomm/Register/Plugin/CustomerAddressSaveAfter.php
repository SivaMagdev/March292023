<?php

namespace Ecomm\Register\Plugin;

use Ecomm\BellNotification\Helper\BellNotification;
use Ecomm\BellNotification\Helper\PushNotification;
use \Psr\Log\LoggerInterface;

class CustomerAddressSaveAfter
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
        $this->logger               = $logger;
    }
    public function afterExecute(\Magento\Customer\Controller\Adminhtml\Address\Save $subject, $result)
    {
        $customerId = $subject->getRequest()->getParam('parent_id', false);
        $addressId = $subject->getRequest()->getParam('entity_id', false);

        $this->logger->log('ERROR','Admin address save:',[$addressId]);

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

        $addressData = $_addressFactory->create()->load($addressId);

        //echo '<pre>'.print_r($addressData->getData(), true).'</pre>';

        $address_status = array_search('Pending', $address_status_value);
        if (!empty($addressData->getCustomAttribute("address_status"))) {
            $address_status = $addressData->getCustomAttribute("address_status")->getValue();
        }

        $customer_repository = $customerRepository->getById($customerId);

        $customer_email = $customer_repository->getEmail();
        $customer_name = $customer_repository->getFirstname();

        if($customer_repository->getDefaultBilling() == $addressId) {
            //$address_type = 'Billing-'.$customer_repository->getDefaultBilling().'-'.$addressId;
            $address_type = 'Billing';
        } else {
            $address_type = 'Shipping';
        }

        $notify_to_customer = 0;
        if (!empty($customer_repository->getCustomAttribute("notify_to_customer"))) {
            $notify_to_customer = $customer_repository->getCustomAttribute("notify_to_customer")->getValue();
        }

        //echo 'address_id: '.$addressId;
        //echo 'customer_id: '.$customerId;
        //echo 'customer_email: '.$customer_email;
        //echo 'address_status: '.$address_status_value[$address_status];

        //exit();



        if($address_status_value[$address_status] == 'Approved' && $notify_to_customer == 1){

            $this->bellNotificationHelper->pushToNotification($addressId, $customerId, 'Address Status Updated', 'Your New Address has been approved');

            // send mobile notification
            $this->pushNotification->sendPushNotification('address', 'Address Status Updated', 'Your New Address has been approved', $customerId);

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
                $templateVars = ['customer_name' => $customer_name, 'address_type' => $address_type];
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

        return $result;
    }
}
?>
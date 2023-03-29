<?php

declare(strict_types=1);

namespace Ecomm\CustomContactus\Plugin;

use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Ecomm\CustomContactus\Helper\Email;

class SentMailPlugin
{
    /**
     * @var Email
     */
    private $helper;

    /**
     * @var ConfigInterface
     */
    private $contactsConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    protected $scopeConfig;

    /**
     * @param \Ecomm\CustomContactus\Helper\Email $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $contactsConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Email $helper,
        ConfigInterface $contactsConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->contactsConfig = $contactsConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->messageManager =$messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Contact\Model\Mail $subject
     * @param [type] $result
     * @param array $variables
     * @param string $replyTo
     * @return void
     */
    public function aroundExecute(
        \Magento\Contact\Controller\Index\Post $subject
    ) {

        $post = $subject->getRequest()->getPostValue();
        if (!$post) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $this->inlineTranslation->suspend();
        try {
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($post);
            

            /*$error = false;

            if (!\Zend_Validate::is(trim($post['name']), 'NotEmpty')) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($post['comment']), 'NotEmpty')) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                $error = true;
            }
            if (\Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
                $error = true;
            }
            if ($error) {
                throw new \Exception();
            }*/

            $adminemail = $this->scopeConfig->getValue('trans_email/ident_support/email',ScopeInterface::SCOPE_STORE);
            $adminname  = $this->scopeConfig->getValue('trans_email/ident_support/name',ScopeInterface::SCOPE_STORE);

            
            $templateVars=[
                "customer_name"=>$post['name'],
                "phone_number"=>$post['telephone'],
                "email_id"=>$post['email'],
                "comment"=>$post['comment']
            ];
           

            if ($this->helper->getCustomerEmailConfirmation()) {
                $toemail=array($post['email']);
                $customeremailtemplate=$this->helper->getCustomerEmailConfirmationTemplate();
                $this->helper->sendMail($customeremailtemplate,$toemail,$templateVars);

                $adminemail = explode(",",$this->helper->getToAdminEmails());               
                $adminemailtemplate=$this->helper->getAdminEmailConfirmationTemplate();
                $this->helper->sendMail($adminemailtemplate,$adminemail,$templateVars);
            }

           
            /*if ($this->helper->getAdminEmailConfirmation()) {
                //$adminemail=array($this->helper->emailAdmin());   
                $admin_email = explode(",",$this->helper->getToAdminEmails());
                print_r($admin_email);
                $adminemailtemplate=$this->helper->getAdminEmailConfirmationTemplate();
                $this->helper->sendMail($adminemailtemplate,$adminemail,$templateVars);
            }*/


            $this->messageManager->addSuccess(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            return $this->resultRedirectFactory->create()->setPath('contact/index');
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.')
            );
            return $this->resultRedirectFactory->create()->setPath('contact/index');
        }
    }
}
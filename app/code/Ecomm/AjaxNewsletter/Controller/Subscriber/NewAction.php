<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @category  PHP
 * @package   Ecommerce_AjaxNewsletter
 * @author    Ishita Sarkar <ishita.sarkar@pwc.com>
 * @copyright 2021 Copyright PwC
 * @license   Private
 */

namespace Ecomm\AjaxNewsletter\Controller\Subscriber;

use Magento\Framework\App\ObjectManager;

class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    protected $resultJson;

    /**
     * New subscription action
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute()
    {
        $result = [];
        $result['error'] = true;
        $result['message'] = __('You are the man.');
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);
                //$om = \Magento\Framework\App\ObjectManager::getInstance();
                //$subscriber_model= $om->create('Ecomm\AjaxNewsletter\Model\Subscriber');

                $subscriber = $this->_subscriberFactory->create()->loadByEmail($email);
                if ($subscriber->getId()
                    && $subscriber->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
                ) {
                    $result['message'] = __('This email address is already subscribed.');
                } else {
                    $status = $this->_subscriberFactory->create()->subscribe($email);
                    
                    if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                        $result['message'] = __('The confirmation request has been sent.');
                        $result['error'] = false;
                    } else {
                        $result['message'] = __('Thank you for your subscription.');
                        $result['error'] = false;

                        $om = \Magento\Framework\App\ObjectManager::getInstance();

                        $name = 'Customer';
                        $customer_factory = $om->get('\Magento\Customer\Model\CustomerFactory');
                        $customer_data = $customer_factory->create();

                        $customer_data->setWebsiteId(1);
                        $customer_data->loadByEmail($email);
                        if($customer_data->getId())
                        {
                            $name = $customer_data->getFirstname();
                        }

                       
                        $templateVar=array("name"=>$name);

                        
                        $helper = $om->get('Ecomm\AjaxNewsletter\Helper\Email');

                        $storeId = $helper->getStoreId();
                        $templateId = $helper->getConfigValue(\Magento\Newsletter\Model\Subscriber::XML_PATH_SUCCESS_EMAIL_TEMPLATE,$storeId);

                        $helper->sendMail($templateId,$email,$templateVar);
                    }
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $result['message'] = __('There was a problem with the subscription: %1', $e->getMessage());
            } catch (\Exception $e) {
                $result['message'] = $e->getMessage();
            }
        }
        return $this->getResultJson()->setData($result);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function getResultJson()
    {
        if ($this->resultJson === null) {
            $this->resultJson = ObjectManager::getInstance()->get(\Magento\Framework\Controller\Result\Json::class);
        }
        return $this->resultJson;
    }
}

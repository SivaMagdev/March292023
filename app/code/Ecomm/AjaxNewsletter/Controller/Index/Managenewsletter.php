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

namespace Ecomm\AjaxNewsletter\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Newsletter\Model\Subscriber;


/**
 * Manage Newsletter
 *
 * @package Ecomm\AjaxNewsletter\Controller\Index
 */
class Managenewsletter extends Action
{
    protected $resultPageFactory;

    protected $session;

    protected $resultRedirectFactory;
	
	protected $_subscriber;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param session $session
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $session,
        RedirectFactory $resultRedirectFactory,
		Subscriber $subscriber
    ) {
    
        parent::__construct($context);
		$this->_subscriber = $subscriber;
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $session;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		
        $customerId = $this->session->getCustomerId();

        //echo $customerId;

        if ($customerId == 0) {
            $this->messageManager->addErrorMessage('Please login to view the service request.');
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('customer/account/login');
                return $resultRedirect;
        } else {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $om->get('Magento\Customer\Model\Session');
            $email = $customerSession->getCustomer()->getEmail();
            $name = $customerSession->getCustomer()->getName();
			
            $subscriber= $om->create('Magento\Newsletter\Model\SubscriberFactory');
            
            $data=$this->getRequest()->getParams('is_subscribed');
            $is_subscribed_val=$data['is_subscribed'];
			
            
            if ($is_subscribed_val) {               
                if ($subscriber->create()->loadByEmail($email)->getId()
                    && $subscriber->create()->loadByEmail($email)->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
                ) {
                    $this->messageManager->addSuccess(__("This email address is already subscribed."));
                } else {
					
                    /*$subscriber = $this->_subscriber;
					$subscriber->subscribe($email);
					$subscriber->save();*/
					
					$this->_subscriber->subscribeCustomerById($customerId);
                    $this->messageManager->addSuccess(__('Thank you for your subscription.'));
                }
            } else {
                //$subscriber->create()->loadByEmail($email)->unsubscribe();
				$this->_subscriber->unsubscribeCustomerById($customerId);
                $this->messageManager->addSuccess(__('You have unsubscribed successfully.'));
            }
        }
        return $this->_redirect('newsletter/manage/');
    }
}

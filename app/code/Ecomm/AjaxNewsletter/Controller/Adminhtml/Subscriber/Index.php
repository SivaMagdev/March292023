<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ecomm\AjaxNewsletter\Controller\Adminhtml\Subscriber;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Newsletter\Controller\Adminhtml\Subscriber as SubscriberAction;

class Index extends \Magento\Newsletter\Controller\Adminhtml\Subscriber\Index
{
    /**
     * Newsletter subscribers page
     *
     * @return void
     */
    public function execute()
    {
       
        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }


        $this->_view->loadLayout();

        $this->_setActiveMenu('Magento_Newsletter::newsletter_subscriber');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Promotional Subscribers'));

        $this->_addBreadcrumb(__('Newsletter'), __('Newsletter'));
        $this->_addBreadcrumb(__('Subscribers'), __('Subscribers'));

        $this->_view->renderLayout();
    }
}

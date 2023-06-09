<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ecomm\AjaxNewsletter\Controller\Adminhtml\Queue;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Newsletter\Controller\Adminhtml\Queue as QueueAction;

/**
 * Show newsletter queue. Needs to be accessible by POST because of filtering.
 */
class Index extends \Magento\Newsletter\Controller\Adminhtml\Queue\Index
{
    /**
     * Queue list action
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->loadLayout();

        $this->_setActiveMenu('Magento_Newsletter::newsletter_queue');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Promotional Queue'));
        $this->_addBreadcrumb(__('Newsletter Queue'), __('Promotional Queue'));

        $this->_view->renderLayout();
    }
}

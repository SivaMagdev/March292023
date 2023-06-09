<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ecomm\AjaxNewsletter\Controller\Adminhtml\Template;


use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\Newsletter\Controller\Adminhtml\Template\Index
{
    /**
     * View Templates list
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
        $this->_setActiveMenu('Magento_Newsletter::newsletter_template');
        $this->_addBreadcrumb(__('Newsletter Templates'), __('Promotional Templates'));
        $this->_addContent(
            $this->_view->getLayout()->createBlock(\Magento\Newsletter\Block\Adminhtml\Template::class, 'template')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Promotional Templates'));
        $this->_view->renderLayout();
    }
}

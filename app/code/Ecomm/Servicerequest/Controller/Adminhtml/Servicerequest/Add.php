<?php

namespace Ecomm\Servicerequest\Controller\Adminhtml\Servicerequest;

use Ecomm\Servicerequest\Controller\Adminhtml\Servicerequest;

class Add extends Servicerequest
{
    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}

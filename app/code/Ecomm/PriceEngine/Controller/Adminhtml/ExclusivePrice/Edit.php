<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\ExclusivePrice;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Edit extends \Ecomm\PriceEngine\Controller\Adminhtml\ExclusivePrice implements HttpGetActionInterface
{
    /**
     * Edit customer group action. Forward to new action.
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        return $this->_resultForwardFactory->create()->forward('new');
    }
}

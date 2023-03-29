<?php
/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_VideoList
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\VideoList\Controller\Adminhtml\Index;

use Magento\Backend\App\Action as ActionClass;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\Model\View\Result\Redirect;
// use BekaertSWSB2B\RequestCertificate\Model\ResourceModel\CertificateRequest\CollectionFactory;
use Ecomm\VideoList\Model\ResourceModel\VideoList\CollectionFactory;

class MassDelete extends ActionClass
{
    /**
     * @var Filter
     */
    protected Filter $filter;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $collection = $this->filter->getCollection(
                $this->collectionFactory->create()
            );
            $collectionSize = $collection->getSize();
            foreach ($collection as $record) {
                $record->delete();
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $collectionSize)
            );
            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while removing the certificates. Error: '.$e->getMessage())
            );
            return $resultRedirect->setPath('*/*/');
        }
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('BekaertSWSB2B_RequestCertificate::requestcertificate_massDelete');
    }
}

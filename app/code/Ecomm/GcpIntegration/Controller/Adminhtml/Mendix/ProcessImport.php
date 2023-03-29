<?php

/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_GcpIntegration
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\GcpIntegration\Controller\Adminhtml\Mendix;

use Magento\Backend\App\Action\Context;
use Magento\Framework\File\Size;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Ecomm\GcpIntegration\Model\Service\ImportService;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\Framework\Escaper;

/**
 * Process controller class
 *
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
class ProcessImport extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ImportService
     */
    private $importService;

    /**
     * @var Size
     */
    private $size;

    /**
     * @var IoFile
     */
    private $io;
    /**
     * @var Import
     */
    private $import;
    /**
     * @var Escaper|null
     */
    protected $escaper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ImportService $importService
     * @param Size $size
     * @param IoFile $io
     * @param Import $import
     * @param Escaper|null $escaper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ImportService $importService,
        Size $size,
        IoFile $io,
        Import $import,
        Escaper $escaper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->importService = $importService;
        $this->size = $size;
        $this->io = $io;
        $this->import = $import;
        $this->escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @throws Exception
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            if ($this->getRequest()->isPost()) {
                $result = $this->importService->processImport();

                $this->messageManager->addSuccessMessage($result);
                return $resultRedirect->setPath('*/*/index');


            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/index');
    }
}

<?php
namespace Ecomm\Invoice\Controller\Customer;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportInvoice extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * ExportInvoice constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ){
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
    }
  public function execute()
  {
      $fileName = 'invoice_report.xlsx';
      $layout = $this->_view->getLayout();
      /** @var $block \Ecomm\Invoice\Block\Export */
      $block = $layout->createBlock(\Ecomm\Invoice\Block\Export::class, 'export.invoice.report', ['cacheable' => "false"]);
      $html = $block->generateReport(true);
      if (!$html) {
          $message = __('No records found.');
          $this->messageManager->addNoticeMessage($message);
          return $this->_redirect($this->_redirect->getRefererUrl());
      }
      $content = ['type' => 'filename', 'value' => $html, 'rm' => true];
      return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
  }
}
<?php
namespace Ecomm\HtmlToPdf\Controller\Test;

use Ecomm\PriceEngine\Model\StockFactory;
use Dompdf\Dompdf;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $dompdf;

    public function __construct(
		\Magento\Framework\App\Action\Context $context,
        Dompdf $dompdf,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->dompdf 		  = $dompdf;
        $this->_pageFactory   = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{

		$this->dompdf->loadHtml('<h1>hello world</h1><hr />');

        // (Optional) Setup the paper size and orientation
        $this->dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $this->dompdf->render();

        // Output the generated PDF to Browser
        $this->dompdf->stream();
	}

}
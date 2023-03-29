<?php
namespace Ecomm\HtmlToPdf\Model;

use Ecomm\HtmlToPdf\Api\TestPdfInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class TestPdf implements TestPdfInterface {

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $directory_list;

    protected $_logo;

    protected $dompdf;

    protected $domoptions;

    protected $resourceConnection;
	public function __construct(
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Dompdf $dompdf,
        Options $domoptions,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->_logo = $logo;
        $this->appEmulation = $appEmulation;
        $this->storeManager = $storeManager;
        $this->directory_list = $directory_list;
        $this->dompdf = $dompdf;
        $this->domoptions = $domoptions;
        $this->resourceConnection = $resourceConnection;
    }

	/**
	 * @return string
	 */
	public function getPdf($order_id)
	{
        //echo '<pre>'.print_r($shippment_data_array, true).'</pre>';

        $html = '';

        $storeId = $this->storeManager->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);

        $html .= '<div><img src="'.$this->_logo->getLogoSrc().'" width="170px"></div>';
        $html .= '<p class="drl-invocie-document">Shipment Document</p>';


        //echo $html; exit();

        $domoptions = $this->dompdf->getOptions();
        $domoptions->setDefaultFont('Courier');
        $domoptions->setIsHtml5ParserEnabled(true);
        $this->domoptions->setIsRemoteEnabled(true);
        $this->dompdf->setOptions($this->domoptions);
        $this->dompdf->setPaper('A4', 'landscape');
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
        $this->dompdf->stream();

        //$pdf_string = $this->dompdf->output();

        //return base64_encode($pdf_string);

	}


}
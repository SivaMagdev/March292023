<?php
namespace Ecomm\Api\Response\Renderer;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response\RendererInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Ecomm\Api\Service\InvoicePdfGeneratorService;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Ecomm\Api\Service\ShippingPdfGeneratorService;

class PdfRenderer implements RendererInterface
{
    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $request;

    /**
     * @var \Ecomm\Api\Service\InvoicePdfGeneratorService
     */
    private $invoicePdfGeneratorService;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var \Ecomm\Api\Service\ShippingPdfGeneratorService
     */
    private $shippingPdfGeneratorService;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    private $shipmentRepository;


    /**
     * Pdf constructor.
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param \Ecomm\Api\Service\InvoicePdfGeneratorService $invoicePdfGeneratorService
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        Request $request,
        InvoicePdfGeneratorService $invoicePdfGeneratorService,
        InvoiceRepositoryInterface $invoiceRepository,
        ShippingPdfGeneratorService $shippingPdfGeneratorService,
        ShipmentRepositoryInterface $shipmentRepository
    ) {
        $this->request = $request;
        $this->invoicePdfGeneratorService = $invoicePdfGeneratorService;
        $this->invoiceRepository = $invoiceRepository;
        $this->shippingPdfGeneratorService = $shippingPdfGeneratorService;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * Render content in a certain format.
     *
     * @param object|array|int|string|bool|float|null $data
     * @return string
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function render($data)
    {
        if (strstr($this->request->getPathInfo(), '/V1/invoices')) {
            if (isset($data['entity_id'])) {
                $invoice = $this->invoiceRepository->get($data['entity_id']);
                $pdf = $this->invoicePdfGeneratorService->execute($invoice);

                return base64_encode($pdf->render());
                // return $pdf->render();
            }
        }

        if (strstr($this->request->getPathInfo(), '/V1/shipment')) {
            if (isset($data['entity_id'])) {
                $shipment = $this->shipmentRepository->get($data['entity_id']);
                $pdf = $this->shippingPdfGeneratorService->execute($shipment);

                return base64_encode($pdf->render());
            }
        }

     
        return null;
    }

    /**
     * Get MIME type generated by renderer.
     *
     * @return string
     */
    public function getMimeType()
    {
        return 'application/pdf';
    }
}
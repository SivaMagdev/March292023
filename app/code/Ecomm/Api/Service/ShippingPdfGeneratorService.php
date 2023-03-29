<?php
namespace Ecomm\Api\Service;

use Magento\Sales\Api\Data\ShipmentInterface;

class ShippingPdfGeneratorService
{
    /**
     * @var \Magento\Sales\Model\Order\Pdf\Shipment
     */
    private $shipmentPdf;

    /**
     * PdfGeneratorService constructor.
     * @param \Magento\Sales\Model\Order\Pdf\Shipment $shipmentPdf
     */
    public function __construct(\Magento\Sales\Model\Order\Pdf\Shipment $shipmentPdf)
    {
        $this->shipmentPdf = $shipmentPdf;
    }

    /**
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @return \Zend_Pdf
     */
    public function execute(ShipmentInterface $shipment)
    {
        return $this->shipmentPdf->getPdf([$shipment]);
    }
}
<?php

namespace Ecomm\HtmlToPdf\Api;

interface ShippingPdfInterface
{
	/**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @param string $shipment_id
     * @return string
     */
    public function getPdf($shipment_id);

    /**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @param string $order_id
     * @return string
     */
    public function getPdfAll($order_id);
}
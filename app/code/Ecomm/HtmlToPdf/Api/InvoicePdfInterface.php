<?php

namespace Ecomm\HtmlToPdf\Api;

interface InvoicePdfInterface
{
	/**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @param string $invoice_id
     * @return string
     */
    public function getPdf($invoice_id);

    /**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @param string $order_id
     * @return string
     */
    public function getPdfAll($order_id);
}
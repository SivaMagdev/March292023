<?php

namespace Ecomm\HtmlToPdf\Api;

interface TestPdfInterface
{
	/**
     * Retrieve list of info
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @param string $order_id
     * @return string
     */
    public function getPdf($order_id);
}
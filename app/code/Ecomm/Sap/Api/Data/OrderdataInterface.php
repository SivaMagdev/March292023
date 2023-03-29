<?php

namespace Ecomm\Sap\Api\Data;

/**
 * @api
 */
interface OrderdataInterface
{
    /**
     * Get order_id
     *
     * @return string
     */
    public function getOrderId();

      /**
     * Set order_id
     *
     * @param string $order_id
     * @return $this
     */
    public function setOrderId($order_id);

    /**
     * Get order_number
     *
     * @return string
     */
    public function getOrderNumber();

      /**
     * Set order_number
     *
     * @param string $order_number
     * @return $this
     */
    public function setOrderNumber($order_number);

    /**
     * Get order_date
     *
     * @return string
     */
    public function getOrderDate();

      /**
     * Set order_date
     *
     * @param string $order_date
     * @return $this
     */
    public function setOrderDate($order_date);

    /**
     * Get order_status
     *
     * @return string
     */
    public function getOrderStatus();

      /**
     * Set order_status
     *
     * @param string $order_status
     * @return $this
     */
    public function setOrderStatus($order_status);

    /**
     * Get order_total
     *
     * @return string
     */
    public function getOrderTotal();

      /**
     * Set order_total
     *
     * @param string $order_total
     * @return $this
     */
    public function setOrderTotal($order_total);

    /**
     * Get payment_term
     *
     * @return string
     */
    public function getPaymentTerm();

      /**
     * Set payment_term
     *
     * @param string $payment_term
     * @return $this
     */
    public function setPaymentTerm($payment_term);

    /**
     * Get delivery_type
     *
     * @return string
     */
    public function getDeliveryType();

      /**
     * Set delivery_type
     *
     * @param string $delivery_type
     * @return $this
     */
    public function setDeliveryType($delivery_type);

    /**
     * Get shipping_cost
     *
     * @return string
     */
    public function getShippingCost();

      /**
     * Set shipping_cost
     *
     * @param string $shipping_cost
     * @return $this
     */
    public function setShippingCost($shipping_cost);

    /**
     * Get shipping_waiver
     *
     * @return string
     */
    public function getShippingWaiver();

      /**
     * Set shipping_waiver
     *
     * @param string $shipping_waiver
     * @return $this
     */
    public function setShippingWaiver($shipping_waiver);

    /**
     * Get customer_id
     *
     * @return string
     */
    public function getCustomerId();

      /**
     * Set customer_id
     *
     * @param string $customer_id
     * @return $this
     */
    public function setCustomerId($customer_id);

    /**
     * Get sap_customer_id
     *
     * @return string
     */
    public function getSapCustomerId();

      /**
     * Set sap_customer_id
     *
     * @param string $sap_customer_id
     * @return $this
     */
    public function setSapCustomerId($sap_customer_id);

    /**
     * Get bill_to_party
     *
     * @return string
     */
    public function getBillToParty();

      /**
     * Set bill_to_party
     *
     * @param string $bill_to_party
     * @return $this
     */
    public function setBillToParty($bill_to_party);

    /**
     * Get ship_to_party
     *
     * @return string
     */
    public function getShipToParty();

      /**
     * Set ship_to_party
     *
     * @param string $ship_to_party
     * @return $this
     */
    public function setShipToParty($ship_to_party);

    /**
     * Get billing_address
     *
     * @return string
     */
    public function getBillingAddress();

      /**
     * Set billing_address
     *
     * @param string $billing_address
     * @return $this
     */
    public function setBillingAddress($billing_address);

    /**
     * Get shipping_address
     *
     * @return string
     */
    public function getShippingAddress();

      /**
     * Set shipping_address
     *
     * @param string $shipping_address
     * @return $this
     */
    public function setShippingAddress($shipping_address);

    /**
     * Get po_number
     *
     * @return string
     */
    public function getPoNumber();

      /**
     * Set po_number
     *
     * @param string $po_number
     * @return $this
     */
    public function setPoNumber($po_number);

    /**
     * Get delivery_date
     *
     * @return string
     */
    public function getDeliveryDate();

      /**
     * Set delivery_date
     *
     * @param string $delivery_date
     * @return $this
     */
    public function setDeliveryDate($delivery_date);

    /**
     * Get delivery_comment
     *
     * @return string
     */
    public function getDeliveryComment();

      /**
     * Set delivery_comment
     *
     * @param string $delivery_comment
     * @return $this
     */
    public function setDeliveryComment($delivery_comment);

    /**
     * Get order_item
     *
     * @return \Ecomm\Sap\Api\Data\OrderItemdataInterface[]
     */
    public function getItem();

      /**
     * Set order_item
     *
     * @param string[] $order_item
     * @return $this
     */
    public function setItem($order_item = array());
}
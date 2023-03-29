<?php

namespace Ecomm\Sap\Model;

class Ordermodel extends \Magento\Framework\Model\AbstractModel implements
    \Ecomm\Sap\Api\Data\OrderdataInterface
{
    const KEY_ORDER_ID          = 'ORDER_ID';

    const KEY_ORDER_NUMBER      = 'ORDER_NUMBER';

    const KEY_ORDER_DATE        = 'ORDER_DATE';

    const KEY_ORDER_STATUS      = 'ORDER_STATUS';

    const KEY_ORDER_TOTAL       = 'ORDER_TOTAL';

    const KEY_PAYMENT_TERM     = 'PAYMENT_TERM';

    const KEY_DELIVERY_TYPE     = 'DELIVERY_COST';

    const KEY_SHIPPING_COST     = 'SHIPPING_COST';

    const KEY_SHIPPING_WAIVER     = 'SHIPPING_WAIVER';

    const KEY_CUSTOMER_ID       = 'CUSTOMER_ID';

    const KEY_SAP_CUSTOMER_ID   = 'SAP_CUSTOMER_ID';

    const KEY_BILL_TO_PARTY     = 'BILL_TO_PARTY';

    const KEY_SHIP_TO_PARTY     = 'SHIP_TO_PARTY';

    const KEY_BILLING_ADDRESS   = 'BILLING_ADDRESS';

    const KEY_SHIPPING_ADDRESS  = 'SHIPPING_ADDRESS';

    const KEY_PO_NUMBER         = 'PO_NUMBER';

    const KEY_DELIVERY_DATE     = 'DELIVERY_DATE';

    const KEY_DELIVERY_COMMENT  = 'DELIVERY_COMMENT';

    const KEY_ITEM = 'ITEM';

     public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getOrderId()
    {
        return $this->_getData(self::KEY_ORDER_ID);
    }

    /**
     * Set order_id
     *
     * @param string $order_id
     * @return $this
     */
    public function setOrderId($order_id)
    {
        return $this->setData(self::KEY_ORDER_ID, $order_id);
    }

    public function getOrderNumber()
    {
        return $this->_getData(self::KEY_ORDER_NUMBER);
    }

    /**
     * Set order_number
     *
     * @param string $order_number
     * @return $this
     */
    public function setOrderNumber($order_number)
    {
        return $this->setData(self::KEY_ORDER_NUMBER, $order_number);
    }


    public function getOrderDate()
    {
        return $this->_getData(self::KEY_ORDER_DATE);
    }

    /**
     * Set order_date
     *
     * @param string $order_date
     * @return $this
     */
    public function setOrderDate($order_date)
    {
        return $this->setData(self::KEY_ORDER_DATE, $order_date);
    }


    public function getOrderStatus()
    {
        return $this->_getData(self::KEY_ORDER_STATUS);
    }

    /**
     * Set order_status
     *
     * @param string $order_status
     * @return $this
     */
    public function setOrderStatus($order_status)
    {
        return $this->setData(self::KEY_ORDER_STATUS, $order_status);
    }


    public function getOrderTotal()
    {
        return $this->_getData(self::KEY_ORDER_TOTAL);
    }

    /**
     * Set order_total
     *
     * @param string $order_total
     * @return $this
     */
    public function setOrderTotal($order_total)
    {
        return $this->setData(self::KEY_ORDER_TOTAL, $order_total);
    }


    public function getPaymentTerm()
    {
        return $this->_getData(self::KEY_PAYMENT_TERM);
    }

    /**
     * Set payment_term
     *
     * @param string $payment_term
     * @return $this
     */
    public function setPaymentTerm($payment_term)
    {
        return $this->setData(self::KEY_PAYMENT_TERM, $payment_term);
    }


    public function getDeliveryType()
    {
        return $this->_getData(self::KEY_DELIVERY_TYPE);
    }

    /**
     * Set delivery_type
     *
     * @param string $delivery_type
     * @return $this
     */
    public function setDeliveryType($delivery_type)
    {
        return $this->setData(self::KEY_DELIVERY_TYPE, $delivery_type);
    }


    public function getShippingCost()
    {
        return $this->_getData(self::KEY_SHIPPING_COST);
    }

    /**
     * Set shipping_cost
     *
     * @param string $shipping_cost
     * @return $this
     */
    public function setShippingCost($shipping_cost)
    {
        return $this->setData(self::KEY_SHIPPING_COST, $shipping_cost);
    }


    public function getShippingWaiver()
    {
        return $this->_getData(self::KEY_SHIPPING_WAIVER);
    }

    /**
     * Set shipping_waiver
     *
     * @param string $shipping_waiver
     * @return $this
     */
    public function setShippingWaiver($shipping_waiver)
    {
        return $this->setData(self::KEY_SHIPPING_WAIVER, $shipping_waiver);
    }


    public function getCustomerId()
    {
        return $this->_getData(self::KEY_CUSTOMER_ID);
    }

    /**
     * Set customer_id
     *
     * @param string $customer_id
     * @return $this
     */
    public function setCustomerId($customer_id)
    {
        return $this->setData(self::KEY_CUSTOMER_ID, $customer_id);
    }


    public function getSapCustomerId()
    {
        return $this->_getData(self::KEY_SAP_CUSTOMER_ID);
    }

    /**
     * Set sap_customer_id
     *
     * @param string $sap_customer_id
     * @return $this
     */
    public function setSapCustomerId($sap_customer_id)
    {
        return $this->setData(self::KEY_SAP_CUSTOMER_ID, $sap_customer_id);
    }


    public function getBillToParty()
    {
        return $this->_getData(self::KEY_BILL_TO_PARTY);
    }

    /**
     * Set bill_to_party
     *
     * @param string $bill_to_party
     * @return $this
     */
    public function setBillToParty($bill_to_party)
    {
        return $this->setData(self::KEY_BILL_TO_PARTY, $bill_to_party);
    }


    public function getShipToParty()
    {
        return $this->_getData(self::KEY_SHIP_TO_PARTY);
    }

    /**
     * Set ship_to_party
     *
     * @param string $ship_to_party
     * @return $this
     */
    public function setShipToParty($ship_to_party)
    {
        return $this->setData(self::KEY_SHIP_TO_PARTY, $ship_to_party);
    }


    public function getBillingAddress()
    {
        return $this->_getData(self::KEY_BILLING_ADDRESS);
    }

    /**
     * Set billing_address
     *
     * @param string $billing_address
     * @return $this
     */
    public function setBillingAddress($billing_address)
    {
        return $this->setData(self::KEY_BILLING_ADDRESS, $billing_address);
    }


    public function getShippingAddress()
    {
        return $this->_getData(self::KEY_SHIPPING_ADDRESS);
    }

    /**
     * Set shipping_address
     *
     * @param string $shipping_address
     * @return $this
     */
    public function setShippingAddress($shipping_address)
    {
        return $this->setData(self::KEY_SHIPPING_ADDRESS, $shipping_address);
    }


    public function getPoNumber()
    {
        return $this->_getData(self::KEY_PO_NUMBER);
    }

    /**
     * Set po_number
     *
     * @param string $po_number
     * @return $this
     */
    public function setPoNumber($po_number)
    {
        return $this->setData(self::KEY_PO_NUMBER, $po_number);
    }


    public function getDeliveryDate()
    {
        return $this->_getData(self::KEY_DELIVERY_DATE);
    }

    /**
     * Set delivery_date
     *
     * @param string $delivery_date
     * @return $this
     */
    public function setDeliveryDate($delivery_date)
    {
        return $this->setData(self::KEY_DELIVERY_DATE, $delivery_date);
    }


    public function getDeliveryComment()
    {
        return $this->_getData(self::KEY_DELIVERY_COMMENT);
    }

    /**
     * Set delivery_comment
     *
     * @param string $delivery_comment
     * @return $this
     */
    public function setDeliveryComment($delivery_comment)
    {
        return $this->setData(self::KEY_DELIVERY_COMMENT, $delivery_comment);
    }


    public function getItem()
    {
        return $this->_getData(self::KEY_ITEM);
    }

    /**
     * Set order_item
     *
     * @param string[] $order_item
     * @return $this
     */
    public function setItem($order_item = array())
    {
        return $this->setData(self::KEY_ITEM, $order_item);
    }
}
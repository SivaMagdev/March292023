<?php
namespace Rage\DeliveryDate\Block\Adminhtml\Order;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;

class DeliveryDate extends Template
{
    public $coreRegistry = null;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }
    public function getOrder() : Order
    {
        return $this->coreRegistry->registry('current_order');
    }
    public function getDeliveryInfo($order)
    {
        $delivery_info = [];
        if ($order->getRgddDeliveryDate() != '0000-00-00') {
            $formatted_date = date_format(date_create($order->getRgddDeliveryDate()), 'M d,Y');
        }
        if (!empty($formatted_date) || !empty($order->getRgddDeliveryComment())) {
            $delivery_info ['date'] = $formatted_date;
            $delivery_info ['note'] = $order->getRgddDeliveryComment();
            return $delivery_info;
        } else {
            return false;
        }
    }
}

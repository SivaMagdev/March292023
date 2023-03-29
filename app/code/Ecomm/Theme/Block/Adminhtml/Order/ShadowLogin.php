<?php

namespace Ecomm\Theme\Block\Adminhtml\Order;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class ShadowLogin extends Template
{
    protected $orderRepository;

    public $coreRegistry = null;

    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    public function getShadowLoginName(){

        $order_id = $this->getRequest()->getParam('order_id');
        $order =  $this->orderRepository->get($order_id);
        return $order->getShadowLoginName();
    }
}
<?php

namespace Ecomm\TiggerFailedOrder\Plugin;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class PluginBtnOrdertoSap
{
    protected $object_manager;
    protected $_backendUrl;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    public function __construct(
        ObjectManagerInterface $om,
        UrlInterface $backendUrl,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->object_manager = $om;
        $this->_backendUrl = $backendUrl;
        $this->orderRepository = $orderRepository;
    }

    public function beforeSetLayout( \Magento\Sales\Block\Adminhtml\Order\View $subject )
    {
        $order = $this->orderRepository->get($subject->getOrderId());
        //echo $order->getBatchId().' - '.$order->getSapId();

        if($order->getBatchId() != '' && $order->getSapId() == '') {

            $sendOrder = $this->_backendUrl->getUrl('ecomm_triggerfailedorder/trigger/index/order_id/'.$subject->getOrderId() );
            $subject->addButton(
                'sendordersms',
                [
                    'label' => __('Resend TO SAP'),
                    'onclick' => "setLocation('" . $sendOrder. "')",
                    'class' => 'ship primary'
                ]
            );
        }

        $subject->removeButton('order_reorder');

        return null;
    }

}
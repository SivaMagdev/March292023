<?php

namespace Ecomm\TiggerFailedOrder\Controller\Adminhtml\Trigger;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;

class MassTrigger extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Sales::cancel';

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface|null $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement = null
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Sales\Api\OrderManagementInterface::class
        );
    }

    /**
     * Cancel selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countQueueOrder = 0;
        foreach ($collection->getItems() as $order) {
            //echo $order->getEntityId().'<br />';
            $_order = $this->orderRepository->get($order->getEntityId());
            //echo $order->getBatchId().' - '.$order->getSapId();

            if($order->getBatchId() != '' && $_order->getSapId() == '') {

                $_order->setBatchId('');
                $_order->save();
                $countQueueOrder++;
            }
        }
        $countNonCancelOrder = $collection->count() - $countQueueOrder;

        if ($countNonCancelOrder && $countQueueOrder) {
            $this->messageManager->addErrorMessage(__('%1 order(s) cannot be sent.', $countNonCancelOrder));
        } elseif ($countNonCancelOrder) {
            $this->messageManager->addErrorMessage(__('You cannot send the order(s).'));
        }

        if ($countQueueOrder) {
            $this->messageManager->addSuccessMessage(__('%1 order(s) - Successfully sent to SAP.', $countQueueOrder));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/order/');
    }
}

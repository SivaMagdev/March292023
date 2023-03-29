<?php
namespace Rage\DeliveryDate\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SaveDeliveryDateToOrderObserver implements ObserverInterface
{
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Psr\Log\LoggerInterface $logger
        ) {
        $this->quote_repository = $quoteRepository;
        $this->logger = $logger;
        $this->date = $dateTime;
    }
    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        $quote = $this->quote_repository->get($order->getQuoteId());

        try {
            $order->setRgddDeliveryDate($quote->getRgddDeliveryDate());
            $order->setRgddDeliveryComment($quote->getRgddDeliveryComment());
            foreach ($quote->getAllItems() as $quoteItem) {
                foreach ($order->getAllItems() as $orderItem) {
                    if ($quoteItem->getPriceType() && $quoteItem->getId() 
                    == $orderItem->getData('quote_item_id')) {
                        $orderItem->setPriceType($quoteItem->getPriceType());
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->addInfo($e);
        }
        return $this;
    }
}

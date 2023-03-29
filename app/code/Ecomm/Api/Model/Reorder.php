<?php

namespace Ecomm\Api\Model;

use Ecomm\Api\Api\ReorderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Quote\Model\Quote;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;

class Reorder implements ReorderInterface
{
    protected $quoteRepository;

    protected $orderRepository;

    protected $quote;

    protected $productRepository;

    protected $cartItemInterface;

    protected $cartItemRepository;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        OrderRepository $orderRepository,
        Quote $quote,
        ProductRepositoryInterface $productRepository,
        CartItemInterface $cartItemInterface,
        CartItemRepositoryInterface $cartItemRepository
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->quote = $quote;
        $this->productRepository = $productRepository;
        $this->cartItemInterface = $cartItemInterface;
        $this->cartItemRepository = $cartItemRepository;
    }


    /**
     * @param $cartId
     * @param $orderId
     * @return bool
     */
    public function createReorder($cartId,$orderId)
    {
       // $quoteRepo = $this->quoteRepository->getActive($cartId);
        $order = $this->orderRepository->get($orderId);

        $items = $order->getItemsCollection();
        foreach ($items as $item) {

            try {
              $this->addOrderItem($item, $cartId);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                return false;

            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    public function addOrderItem($orderItem,$quoteId)
    {
        /* @var $orderItem \Magento\Sales\Model\Order\Item */
        if ($orderItem->getParentItem() === null) {
            //$storeId = $this->_storeManager->getStore()->getId();
            try {
                /**
                 * We need to reload product in this place, because products
                 * with the same id may have different sets of order attributes.
                 */
                $product = $this->productRepository->getById($orderItem->getProductId());

            } catch (NoSuchEntityException $e) {
                return $this;
            }

            $this->cartItemInterface->setQuoteId($quoteId);
            $this->cartItemInterface->setSku($product->getSku());
            $this->cartItemInterface->setQty($orderItem->getQtyOrdered());

            $this->cartItemRepository->save($this->cartItemInterface);

        }
    }

}
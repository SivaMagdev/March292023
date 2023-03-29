<?php
namespace Ecomm\Theme\Plugin;

use Magento\Checkout\Model\Cart;
use Magento\Wishlist\Model\Item;

class KeepItemsInWishlistOnPurchase
{
    /**
     * @param \Magento\Wishlist\Model\Item $item
     * @param \Magento\Checkout\Model\Cart $cart
     * @return array
     */
    public function beforeAddToCart(Item $item, Cart $cart)
    {
        return [$cart, false];
    }
}
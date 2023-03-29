<?php

namespace Ecomm\Theme\Block\Cart\Item\Renderer\Actions;

class MoveToWishlist extends \Magento\Wishlist\Block\Cart\Item\Renderer\Actions\MoveToWishlist
{
    public function checkAddedInoWishlist($product_id)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_wishlistHelper = $objectManager->get('\Magento\Wishlist\Helper\Data');

    	$wishlistcollection = clone $_wishlistHelper->getWishlistItemCollection();
    	$wdata = $wishlistcollection->addFieldToFilter('main_table.product_id', $product_id);

    	if($wdata->getData()) {
    		//echo '<pre>'.print_r($wdata->getData(), true).'</pre>'.$_product->getId();

    		return true;
    	}

    	return false;
    }

    public function getProduct()
    {
        return $this->getItem();
    }
}
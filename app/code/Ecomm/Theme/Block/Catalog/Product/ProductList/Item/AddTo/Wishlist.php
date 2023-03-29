<?php

namespace Ecomm\Theme\Block\Catalog\Product\ProductList\Item\AddTo;

class Wishlist extends \Magento\Wishlist\Block\Catalog\Product\ProductList\Item\AddTo\Wishlist
{
    public function checkAddedInoWishlist($_product)
    {

    	if($_product->getTypeId() == 'simple'){
            $wishlistcollection = clone $this->_wishlistHelper->getWishlistItemCollection();
    	   $wdata = $wishlistcollection->addFieldToFilter('main_table.product_id', $_product->getId());

        	if($wdata->getData()) {
        		//echo '<pre>'.print_r($wdata->getData(), true).'</pre>'.$_product->getId();
        		return 'active';
        	}
        }

    	return false;
    }
}
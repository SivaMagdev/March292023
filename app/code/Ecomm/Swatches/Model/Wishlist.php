<?php

namespace Ecomm\Swatches\Model;

use Magento\Framework\Model\AbstractModel;

class Wishlist extends AbstractModel
{


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    protected function _construct()
    {
        $this->_init('Ecomm\Swatches\Model\ResourceModel\Wishlist');
    }

    public function getCustomerWishlist($customerid)
    {
        $getCustomerWishlist = $this->getCollection()
            ->join('wishlist', 'main_table.wishlist_id = wishlist.wishlist_id');

        $getCustomerWishlist->getSelect()->where('wishlist.customer_id='.(int)$customerid)->group('main_table.product_id');

        return $getCustomerWishlist;

    }

    public function getCustomerProductWishlist($customerid, $productid)
    {
        $getCustomerWishlist = $this->getCollection()
            ->join('wishlist', 'main_table.wishlist_id = wishlist.wishlist_id');

        $getCustomerWishlist->getSelect()->where('wishlist.customer_id='.(int)$customerid)
        ->where('main_table.product_id='.$productid)
        ->group('main_table.product_id');

        return $getCustomerWishlist;
    }
}

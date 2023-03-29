<?php

namespace Ecomm\Swatches\Model;

class ModelWishlist extends  \Magento\Wishlist\Model\Wishlist
{
    /**
     * Retrieve wishlist item collection
     *
     * @return \Magento\Wishlist\Model\ResourceModel\Item\Collection
     * @throws NoSuchEntityException
     */
    public function getItemCollection()
    {
        if ($this->_itemCollection === null) {

            $this->_itemCollection = $this->_wishlistCollectionFactory->create()->addWishlistFilter(
                $this
            )->addStoreFilter(
                $this->getSharedStoreIds()
            );
        }

        return $this->_itemCollection;
    }
}

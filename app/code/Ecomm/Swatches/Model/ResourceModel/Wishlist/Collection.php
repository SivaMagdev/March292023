<?php


namespace Ecomm\Swatches\Model\ResourceModel\Wishlist;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Ecomm\Swatches\Model\Wishlist::class, \Ecomm\Swatches\Model\ResourceModel\Wishlist::class);
    }
}

<?php


namespace Ecomm\Swatches\Model\ResourceModel;

class Wishlist extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('wishlist_item', 'wishlist_item_id');
    }
}

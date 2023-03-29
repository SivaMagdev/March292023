<?php
/**
 * @copyright: Copyright © 2017 mediaman GmbH. All rights reserved.
 * @see LICENSE.txt
 */

namespace Ecomm\Api\Model;

use Ecomm\Api\Api\WishlistInterface;

/**
 * Class Wishlist
 * @package Ecomm\Api\Model
 */
class Wishlist extends \Magento\Wishlist\Model\Wishlist implements WishlistInterface
{

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->getItemCollection()->getItems();
    }
}

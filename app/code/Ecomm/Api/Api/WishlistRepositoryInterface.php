<?php
/**
 * @copyright: Copyright © 2017 mediaman GmbH. All rights reserved.
 * @see LICENSE.txt
 */

namespace Ecomm\Api\Api;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface WishlistRepositoryInterface
 * @package Ecomm\Api\Api
 * @api
 */
interface WishlistRepositoryInterface
{

    /**
     * Get the current customers wishlist
     *
     * @return \Magento\Wishlist\Model\WishlistFactory
     * @throws NoSuchEntityException
     */
    public function getCurrent();


    /**
     * Get the current customers wishlist
     *
     * @return WishlistData
     * @throws NoSuchEntityException
     */
    public function getWishlistForCustomer();

    /**
     * Add an item from the customers wishlist
     *
     * @param string $sku
     * @return bool
     */
    public function addItem(string $sku): bool;

    /**
     * Remove an item from the customers wishlist
     *
     * @param int $itemId
     * @return boolean
     * @throws NoSuchEntityException
     */
    public function removeItem(int $itemId): bool;

    /**
     * Remove an item from the customers wishlist
     *
     * @param int $itemId
     * @param int $qty
     * @return boolean
     * @throws NoSuchEntityException
     */
    public function updateItem(int $itemId,int $qty);
}

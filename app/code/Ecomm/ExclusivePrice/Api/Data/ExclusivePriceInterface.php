<?php

namespace Ecomm\ExclusivePrice\Api\Data;
interface ExclusivePriceInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const EXCLUSIVE_PRICE_ID = 'exclusive_price_id';
    const PRODUCT_SKU = 'product_sku';
    const NDC = 'ndc';
    const NAME = 'name';
    const CUSTOMER_ID = 'customer_id';
    const PRICE = 'price';
    // const CREATED_AT = 'created_at';
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getExclusivePriceId();
    /**
     * Set EntityId.
     */
    public function setExclusivePriceId($exclusivePriceId);
    /**
     * Get Title.
     *
     * @return varchar
     */
    public function getProductSku();
    /**
     * Set Title.
     */
    public function setProductSku($productSku);
    /**
     * Get Content.
     *
     * @return varchar
     */
    public function getNdc();
    /**
     * Set Content.
     */
    public function setNdc($ndc);
    /**
     * Get Publish Date.
     *
     * @return varchar
     */
    public function getCustomerId();
    /**
     * Set PublishDate.
     */
    public function setCustomerId($customerId);
    /**
     * Get IsActive.
     *
     * @return varchar
     */
    public function getName();
    /**
     * Set StartingPrice.
     */
    public function setName($name);
    /**
     * Get UpdateTime.
     *
     * @return varchar
     */
    public function getPrice();
    /**
     * Set UpdateTime.
     */
    public function setPrice($price);
    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    // public function getCreatedAt();
    // /**
    //  * Set CreatedAt.
    //  */
    // public function setCreatedAt($createdAt);
}
<?php

namespace Ecomm\Api\Api\Data;

/**
 * @api
 */
interface AttributeOptionsdataInterface
{
    /**
     * Get label
     *
     * @return string
     */
    public function getLabel();

      /**
     * Set label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Get value
     *
     * @return string
     */
    public function getValue();

      /**
     * Set value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Get product_count
     *
     * @return string
     */
    public function getProductCount();

      /**
     * Set product_count
     *
     * @param string $product_count
     * @return $this
     */
    public function setProductCount($product_count);
}
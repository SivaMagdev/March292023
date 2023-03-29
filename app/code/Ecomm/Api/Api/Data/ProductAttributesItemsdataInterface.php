<?php

namespace Ecomm\Api\Api\Data;

/**
 * @api
 */
interface ProductAttributesItemsdataInterface
{

    /**
     * Get items
     *
     * @return \Ecomm\Api\Api\Data\AttributeItemsdataInterface[]
     */
    public function getItems();

      /**
     * Set items
     *
     * @param string[] $items
     * @return $this
     */
    public function setItems($items);

}
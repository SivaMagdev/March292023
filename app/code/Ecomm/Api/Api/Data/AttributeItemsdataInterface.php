<?php

namespace Ecomm\Api\Api\Data;

/**
 * @api
 */
interface AttributeItemsdataInterface
{
    /**
     * Get options
     *
     * @return \Ecomm\Api\Api\Data\AttributeOptionsdataInterface[]
     */
    public function getOptions();

      /**
     * Set options
     *
     * @param string[] $options
     * @return $this
     */
    public function setOptions($options = array());
}
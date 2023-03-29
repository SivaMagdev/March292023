<?php

namespace Ecomm\Servicerequest\Api\Servicerequest;

use Magento\Framework\Api\SearchResultsInterface;

interface ServicerequestSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get data list.
     *
     * @return \Ecomm\Servicerequest\Api\Servicerequest\ServicerequestInterface[]
     */
    public function getItems();

    /**
     * Set data list.
     *
     * @param \Ecomm\Servicerequest\Api\Servicerequest\ServicerequestInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

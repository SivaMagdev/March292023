<?php

namespace Ecomm\ExclusivePrice\Block\Product;

class ProductsList extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    protected function getCacheLifetime()
    {
        return null;
    }
}
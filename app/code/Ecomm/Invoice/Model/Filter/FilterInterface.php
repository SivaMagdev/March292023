<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecomm\Invoice\Model\Filter;

use Magento\Sales\Model\ResourceModel\Order\Collection;

/**
 * Interface FilterInterface
 */
interface FilterInterface
{
    /**
     * Apply filter for provided collection
     *
     * @param Collection $ordersCollection
     * @param mixed $value
     *
     * @return Collection
     */
    public function applyFilter(Collection $ordersCollection, $value): Collection;
}

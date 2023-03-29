<?php
/**
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license/  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\CartReservation\Model;

/**
 * It keeps filters that are added to the collection to avoid adding them twice.
 *
 * @since 2.3.7
 */
class CategoryActiveFiltersRegistry
{
    private $activeFilters = [];

    /**
     * @param string $filterName
     */
    public function addActiveFilter(string $filterName): void
    {
        if (! $this->isActiveFilter($filterName)) {
            $this->activeFilters[] = $filterName;
        }
    }

    /**
     * @param string $filterName
     * @return bool
     */
    public function isActiveFilter(string $filterName): bool
    {
        return in_array($filterName, $this->activeFilters, true);
    }
}

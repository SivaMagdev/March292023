<?php
/**
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license/  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\CartReservation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Plumrocket\CartReservation\Model\CategoryActiveFiltersRegistry;

class CategoryCollectionAddIsActiveFilter implements ObserverInterface
{
    private $categoryActiveFiltersRegistry;

    public function __construct(
        CategoryActiveFiltersRegistry $categoryActiveFiltersRegistry
    ) {
        $this->categoryActiveFiltersRegistry = $categoryActiveFiltersRegistry;
    }

    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $this->categoryActiveFiltersRegistry->addActiveFilter('is_active');
    }
}

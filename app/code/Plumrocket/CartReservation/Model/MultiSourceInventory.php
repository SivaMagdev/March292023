<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
declare(strict_types=1);

namespace Plumrocket\CartReservation\Model;

/**
 * Class MultiSourceInventory
 *
 * TODO: refactor code after left support 2.2
 */
class MultiSourceInventory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $stockId = [];

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * MultiSourceInventory constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param string $websiteCode
     * @return bool
     */
    public function isEnabled(string $websiteCode) : bool
    {
        if (! array_key_exists($websiteCode, $this->stockId)) {
            if ($this->moduleManager->isEnabled('Magento_InventorySales')) {
                /** @var \Magento\InventorySales\Model\ResourceModel\StockIdResolver $stockIdResolver */
                $stockIdResolver = $this->objectManager->get('\Magento\InventorySales\Model\ResourceModel\StockIdResolver');
                $this->stockId[$websiteCode] = $stockIdResolver->resolve('website', $websiteCode);
            } else {
                $this->stockId[$websiteCode] = false;
            }
        }

        return (bool) $this->stockId[$websiteCode];
    }

    /**
     * @param array  $productIds
     * @param string $websiteCode
     * @return array
     */
    public function getQtyByIds(array $productIds, string $websiteCode) : array
    {
        if (! $this->isEnabled($websiteCode)) {
            return [];
        }

        /** @var \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $getSalableQuantityDataBySku */
        $getSalableQuantityDataBySku = $this->objectManager->create("Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku");
        /** @var \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface $getSkusByProductIds */
        $getSkusByProductIds = $this->objectManager->get('\Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface');

        $productSkus = $getSkusByProductIds->execute($productIds);

        $result = [];

        foreach ($productSkus as $id => $sku) {
            try {
                foreach ($getSalableQuantityDataBySku->execute($sku) as $item) {
                    $count = $item['qty'];
                }
                $result[$id] = $count;
            } catch (\Exception $e) {
                $result[$id] = 0;
            }
        }

        return $result;
    }
}

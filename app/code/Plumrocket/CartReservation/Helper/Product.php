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
 * @package     Plumrocket Cart Reservation v2.x.x
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\CartReservation\Helper;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Plumrocket\CartReservation\Model\Attribute\Source\Enable;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Product attribute code
     */
    const ATTRIBUTE_CODE = 'pr_cartreservation_enable';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockState;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var Bundle
     */
    protected $bundle;

    /**
     * @var \Plumrocket\CartReservation\Model\MultiSourceInventory
     */
    private $multiSourceInventory;

    /**
     * @var \Plumrocket\CartReservation\Model\CategoryActiveFiltersRegistry
     */
    private $categoryActiveFiltersRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context                           $context
     * @param \Magento\Framework\App\ResourceConnection                       $resource
     * @param \Magento\Framework\Registry                                     $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                     $dateTime
     * @param \Magento\CatalogInventory\Api\StockStateInterface               $stockState
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                 $productRepository
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface                $categoryRepository
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable    $configurable
     * @param \Magento\Bundle\Model\Product\Type                              $bundle
     * @param \Plumrocket\CartReservation\Model\MultiSourceInventory          $multiStockInventory
     * @param \Plumrocket\CartReservation\Model\CategoryActiveFiltersRegistry $categoryActiveFiltersRegistry
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        Configurable $configurable,
        Bundle $bundle,
        \Plumrocket\CartReservation\Model\MultiSourceInventory $multiStockInventory,
        \Plumrocket\CartReservation\Model\CategoryActiveFiltersRegistry $categoryActiveFiltersRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resource = $resource;
        $this->coreRegistry = $coreRegistry;
        $this->dateTime = $dateTime;
        $this->stockState = $stockState;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->configurable = $configurable;
        $this->bundle = $bundle;
        $this->multiSourceInventory = $multiStockInventory;
        $this->categoryActiveFiltersRegistry = $categoryActiveFiltersRegistry;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve reservation data for products
     *
     * @param  int|array $productIds
     * @param  int|array $excludeQuoteIds
     * @param  int|array $includeQuoteIds
     * @return array
     */
    public function getReservations($productIds, $excludeQuoteIds = null, $includeQuoteIds = null)
    {
        if (! is_array($productIds)) {
            $productIds = [$productIds];
        }

        $connection = $this->resource->getConnection();
        $query = $connection->select()
            ->from(
                [
                    'q' => $this->resource->getTableName('quote_item')
                ],
                [
                    'q.product_id',
                    'MIN(q.timer_expire_at) AS timer_expire_at',
                    // Reserved qty for configurable and bundle products need to be the multiply child qty and parent qty.
                    // As version, for optimization we can calculate reserved qty by formula "base_row_total / base_price".
                    'SUM(q.qty * IFNULL(q2.qty, 1)) AS reserved_qty',
                    'q.quote_id'
                ]
            )
            ->joinLeft(
                [
                    'q2' => $this->resource->getTableName('quote_item')
                ],
                'q2.item_id = q.parent_item_id',
                [
                    'q2.product_id AS parent_product_id'
                ]
            )
            ->joinLeft(
                [
                    's' => $this->resource->getTableName('cataloginventory_stock_item')
                ],
                's.product_id = q.product_id',
                [
                    's.qty AS max_qty', // not working for Multi Stock Inventory in magento 2.3.*
                    's.min_qty AS min_qty',
                ]
            )
            ->where('q.timer_expire_at > ?', $this->dateTime->gmtTimestamp())
            // Skip items without max_qty (e.g. for downloadable product)
            ->where('s.qty IS NOT NULL')
            ->group('q.product_id');

        if ($excludeQuoteIds) {
            if (! is_array($excludeQuoteIds)) {
                $excludeQuoteIds = [$excludeQuoteIds];
            }

            $query->where('q.quote_id NOT IN(?)', $excludeQuoteIds);
        }

        if ($includeQuoteIds) {
            if (! is_array($includeQuoteIds)) {
                $includeQuoteIds = [$includeQuoteIds];
            }

            $query->where('(q.product_id IN(?)', $productIds)->orWhere('q.quote_id IN(?))', $includeQuoteIds);
        } else {
            $query->where('q.product_id IN(?)', $productIds);
        }

        $items = $connection->fetchAll($query);

        $result = [];
        foreach ($items as $item) {
            $result[$item['product_id']] = $item;
        }

        return $this->applyMultiSourceInventoryQty($result);
    }

    /**
     * Retrieve timers for products
     *
     * @param  int|array $productIds
     * @param  int|array $excludeQuoteIds
     * @param  int|array $includeQuoteIds
     * @return array
     */
    public function getTimers($productIds, $excludeQuoteIds = null, $includeQuoteIds = null)
    {
        $items = $this->getReservations($productIds, $excludeQuoteIds, $includeQuoteIds);

        $timers = [];
        $children = [];
        foreach ($items as $item) {
            $item['reserved_qty'] = (float) $item['reserved_qty'];

            if ($item['min_qty'] <= 0
                && $item['reserved_qty'] < $item['max_qty']
            ) {
                continue;
            }

            if ($item['min_qty'] > 0
                && $item['reserved_qty'] < $item['max_qty'] - $item['min_qty']
            ) {
                continue;
            }

            $timers[$item['product_id']] = $item;

            // Calculate reserved children.
            if ($parentId = $item['parent_product_id']) {
                if (empty($children[$parentId])) {
                    $children[$parentId] = 0;
                }
                $children[$parentId]++;
            }
        }

        // Set count of reserved children to timers.
        foreach ($children as $productId => $count) {
            if (isset($timers[$productId])) {
                $timers[$productId]['reserved_children_count'] = $count;
            }
        }

        return $timers;
    }

    /**
     * Retrieve product id and them children
     *
     * @param ProductModel $product
     * @return int[]
     */
    public function getAllIds(ProductModel $product = null)
    {
        if (null === $product) {
            $product = $this->coreRegistry->registry('current_product');
        }

        $productIds = [];
        if ($product && $product->getId()) {
            $productIds[] = $product->getId();

            if ($groupIds = $product->getTypeInstance()->getChildrenIds($product->getId(), false)) {
                foreach ($groupIds as $groupId => $childrenIds) {
                    $productIds = array_merge($productIds, $childrenIds);
                }

                $productIds = array_unique($productIds);
            }
        }

        return $productIds;
    }

    /**
     * Check if reservation is enabled for product
     *
     * @param Product|int $product
     * @param bool $checkParent
     * @return bool|null
     */
    public function reservationEnabled($product, $checkParent = null)
    {
        if (is_numeric($product)) {
            $product = $this->productRepository->getById($product);
        }

        if (null === $checkParent) {
            $checkParent = $this->isChild($product);
        }

        $enabled = null;
        if (Enable::INHERITED == $product->getData(self::ATTRIBUTE_CODE)
            || null === $product->getData(self::ATTRIBUTE_CODE)
        ) {
            // Check parents for configurable/bundle products.
            if ($checkParent) {
                $parentIds = $this->getParentIdsByChild($product);
                foreach ($parentIds as $parentId) {
                    $_enabled = $this->reservationEnabled($parentId, false);
                    if (null !== $_enabled) {
                        $enabled = $_enabled;
                        break;
                    }
                }
            }

            // Check categories.
            if (null === $enabled) {
                // Get current category.
                if ($category = $product->getCategory()) {
                    $enabled = $this->reservationEnabledByCategory($category);
                } else {
                    $categories = $product
                        ->getCategoryCollection()
                        ->addAttributeToSelect(self::ATTRIBUTE_CODE);

                    if (! $this->categoryActiveFiltersRegistry->isActiveFilter('is_active')) {
                        $categories->addIsActiveFilter();
                    }

                    foreach ($categories as $category) {
                        $_enabled = $this->reservationEnabledByCategory($category);
                        if (null !== $_enabled) {
                            $enabled = $_enabled;
                            break;
                        }
                    }
                }
            }
        } else {
            $enabled = Enable::YES == $product->getData(self::ATTRIBUTE_CODE);
        }

        return $enabled;
    }

    /**
     * Check if is reservation ebabled in parent categories
     *
     * @param  \Magento\Catalog\Model\Category|int $category
     * @return bool|null
     */
    public function reservationEnabledByCategory($category)
    {
        if (is_numeric($category)) {
            try {
                $category = $this->categoryRepository->get($category);
            } catch (\Exception $e) {
                return null;
            }
        }

        $enabled = null;
        if (Enable::INHERITED == $category->getData(self::ATTRIBUTE_CODE)
            || null === $category->getData(self::ATTRIBUTE_CODE)
        ) {
            // getParentCategory always returns object, so if use it then need to check $parentCategory->getId()
            if ($parentCategoryId = $category->getParentId()) {
                $enabled = $this->reservationEnabledByCategory($parentCategoryId);
            }
        } else {
            $enabled = Enable::YES == $category->getData(self::ATTRIBUTE_CODE);
        }

        return $enabled;
    }

    /**
     * Check if product is child
     *
     * @param  ProductModel $product
     * @return boolean
     */
    public function isChild(ProductModel $product)
    {
        return ! $product->isVisibleInSiteVisibility()
            && ! in_array($product->getTypeId(), [Configurable::TYPE_CODE, Bundle::TYPE_CODE]);
    }

    /**
     * Find parent product id for product
     *
     * @param  ProductModel $product
     * @return array
     */
    public function getParentIdsByChild(ProductModel $product)
    {
        $parentIds = $product->getTypeInstance()->getParentIdsByChild($product->getId());

        if (! $parentIds) {
            $parentIds = $this->configurable->getParentIdsByChild($product->getId());
        }

        if (! $parentIds) {
            $parentIds = $this->bundle->getParentIdsByChild($product->getId());
        }

        return $parentIds;
    }

    /**
     * Retrieve stock qty whether product is composite or no
     *
     * @param  int $productId
     * @param  int $scopeId
     * @return float
     */
    public function getProductQty($productId, $scopeId = null)
    {
        return $this->stockState->getStockQty($productId, $scopeId);
    }

    /**
     * Get is virtual
     *
     * @param ProductModel|int $product
     * @return bool
     */
    public function isVirtual($product)
    {
        if (is_numeric($product)) {
            $product = $this->productRepository->getById($product);
        }

        return $product->getIsVirtual()
            || $product->getTypeId() == Downloadable::TYPE_DOWNLOADABLE;
    }

    /**
     * @param array $result
     * @return array
     */
    private function applyMultiSourceInventoryQty(array $result) : array
    {
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        if ($this->multiSourceInventory->isEnabled($websiteCode)) {
            $qtyFromMsi = $this->multiSourceInventory->getQtyByIds(array_keys($result), $websiteCode);

            foreach ($result as $id => $info) {
                if (isset($qtyFromMsi[$id])) {
                    $result[$id]['max_qty'] = $qtyFromMsi[$id];
                }
            }
        }

        return $result;
    }
}

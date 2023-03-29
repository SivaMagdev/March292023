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
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\CartReservation\Helper;

use Plumrocket\CartReservation\Model\Config\Source\StartAction;
use Plumrocket\CartReservation\Model\Config\Source\TimerMode;
use Plumrocket\CartReservation\Model\Config\Source\TimerType;

class Item extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Reservation disabled for product by product/category configuration
     */
    const RESERVATION_DISABLED = 1;

    /**
     * Reservation disabled for guest's items
     */
    const RESERVATION_GUEST_DISABLED = 2;

    /**
     * @var array
     */
    protected $reservationStatuses = [
        self::RESERVATION_DISABLED,
        self::RESERVATION_GUEST_DISABLED,
    ];

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Plumrocket\CartReservation\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context              $context
     * @param \Magento\Framework\App\ResourceConnection          $resource
     * @param \Plumrocket\CartReservation\Helper\Data            $dataHelper
     * @param \Plumrocket\CartReservation\Helper\Config          $configHelper
     * @param \Plumrocket\CartReservation\Helper\Product         $productHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime        $dateTime
     * @param \Magento\Framework\Session\SessionManagerInterface $checkoutSession
     * @param \Magento\Quote\Model\QuoteRepository               $quoteRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        Data $dataHelper,
        \Plumrocket\CartReservation\Helper\Config $configHelper,
        \Plumrocket\CartReservation\Helper\Product $productHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Session\SessionManagerInterface $checkoutSession,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        parent::__construct($context);
        $this->resource = $resource;
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->productHelper = $productHelper;
        $this->dateTime = $dateTime;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Set max items time as global time
     *
     * @param  int $quoteId
     * @param  int|array $exceptItemIds
     * @param  string $timerMode
     * @param  int $maxExpireAt
     * @return bool
     */
    public function updateGlobalTimer($quoteId, $exceptItemIds = [], $timerMode = null, $maxExpireAt = null)
    {
        if (! is_numeric($quoteId)) {
            return false;
        }

        if (! in_array($timerMode, [Data::TIMER_MODE_CART, Data::TIMER_MODE_CHECKOUT])) {
            $timerMode = $this->dataHelper->getTimerMode() ?: Data::TIMER_MODE_CART;
        }

        if (Data::TIMER_MODE_CART === $timerMode
            && $this->configHelper->getCartReservationType() != TimerType::TYPE_GLOBAL
        ) {
            return false;
        }

        $connection = $this->resource->getConnection();

        // Get max expiry time as global time.
        if (null === $maxExpireAt) {
            $query = $connection->select()
                ->from(
                    [
                        'q' => $this->resource->getTableName('quote_item')
                    ],
                    [
                        "MAX(`original_{$timerMode}_expire_at`) AS max_expire_at"
                    ]
                )
                ->where('q.quote_id = ?', $quoteId);

            if ($exceptItemIds) {
                if (! is_array($exceptItemIds)) {
                    $exceptItemIds = [$exceptItemIds];
                }

                // Exclude items, e.g. which will deleted.
                $query->where('q.item_id NOT IN (?)', join(',', $exceptItemIds));
                $query->where('q.parent_item_id IS NULL OR q.parent_item_id NOT IN (?)', join(',', $exceptItemIds));
            }

            $maxExpireAt = $connection->fetchOne($query);
        }

        // Try to update all items (expired and non-expired).
        // Non-expired items can to update by one update query.
        // But sometimes after thet object of item has been saved with old timer_expire_at and overwrites query data.
        $items = $this->quoteRepository->get($quoteId)->getItemsCollection();

        foreach ($items as $item) {
            if ($item->getData('prcr_time_updated')) {
                continue;
            }

            if ($item->getData('timer_expire_at') == $maxExpireAt) {
                continue;
            }

            // Skip items with special reservation statuses. Can't it do like:
            // ->addFieldToFilter("original_{$timerMode}_expire_at", ['nin' => join(',', $this->getReservationStatuses())]);
            // because collection might be loaded previously and therefore it will ignore additional conditions.
            if ($this->getReservationStatuses($item->getData("original_{$timerMode}_expire_at"))) {
                continue;
            }

            // Quote can have two separately product that are expired, but only one can to continue.
            // So, every item need to check and save separately.
            if ($item->getData("original_{$timerMode}_expire_at") <= $this->dateTime->gmtTimestamp()
                && $this->productHelper->getTimers($item->getProductId())
            ) {
                continue;
            }

            // Update parent item with its child because parent items always have timer (max_qty = 0) and don't enter this block.
            $this->updateItem($item, [
                'timer_expire_at' => $maxExpireAt,
                'prcr_time_updated' => true
            ]);
        }

        return true;
    }

    /**
     * Update data of item
     *
     * @param  \Magento\Quote\Model\Quote\Item $item
     * @param  array $data
     * @param  boolean $updateParent
     * @return boolean
     */
    public function updateItem($item, $data, $updateParent = true)
    {
        $item->addData($data);
        if (! $item->isObjectNew()) {
            $item->save();
        }

        if ($updateParent && $item->getParentItemId()) {
            $parentItem = $this->getQuoteItems($item->getQuoteId())
                ->getItemById($item->getParentItemId());
            if ($parentItem) {
                $parentItem->addData($data)->save();
            }
        }

        return true;
    }

    /**
     * Get items collection
     *
     * @param  int $quoteId
     * @return array|\Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    public function getQuoteItems($quoteId = null)
    {
        if (null === $quoteId) {
            $quoteId = $this->getQuoteId();
        }

        try {
            $items = $this->quoteRepository->get($quoteId)->getItemsCollection();
        } catch (\Exception $e) {
            $items = [];
        }

        return $items;
    }

    /**
     * Get current quote id
     *
     * @return int
     */
    public function getQuoteId()
    {
        return $this->checkoutSession->getQuoteId();
    }

    /**
     * Get current global time
     *
     * @param  null|int $quoteId
     * @return int
     */
    public function getGlobalTime($quoteId = null)
    {
        if (null === $quoteId) {
            $quoteId = $this->getQuoteId();
        }

        if (! $quoteId) {
            return 0;
        }

        $connection = $this->resource->getConnection();
        $query = $connection->select()
            ->from(
                [
                    'q' => $this->resource->getTableName('quote_item')
                ],
                [
                    'MAX(q.timer_expire_at) AS timer_expire_at'
                ]
            )
            ->where('q.quote_id = ?', $quoteId);

        return $connection->fetchOne($query);
    }

    /**
     * Retrieve reservation statuses
     *
     * @param int|null $code
     * @return array
     */
    public function getReservationStatuses($code = null)
    {
        if (null !== $code) {
            return in_array($code, $this->reservationStatuses);
        }

        return $this->reservationStatuses;
    }

    /**
     * Get reservation status
     *
     * @param  \Magento\Quote\Model\Quote\Item $item
     * @return int
     */
    public function getReservationStatus($item)
    {
        $code = $item->getData('timer_expire_at');
        return $this->getReservationStatuses($code) ? $code : 0;
    }

    /**
     * Set reservation status
     *
     * @param  \Magento\Quote\Model\Quote\Item $item
     * @param  int $code
     * @return bool
     */
    public function setReservationStatus($item, $code)
    {
        $item->setData('timer_expire_at', $code);

        return true;
    }

    /**
     * Change mode
     *
     * @param  string $mode
     * @return bool
     */
    public function switchMode($mode)
    {
        if (! in_array($mode, [Data::TIMER_MODE_CART, Data::TIMER_MODE_CHECKOUT])) {
            return false;
        }

        if ($this->dataHelper->getTimerMode() == $mode) {
            return true;
        }
        $this->dataHelper->setTimerMode($mode);

        $set = [];

        // Cart -> Checkout.
        if ($mode === Data::TIMER_MODE_CHECKOUT) {
            if ($this->configHelper->getTimerMode() == TimerMode::SEPARATE) {
                $expireAt = $this->dataHelper->getExpireAt(
                    $this->configHelper->getCheckoutTime()
                );

                if ($this->configHelper->getCheckoutStartAction() == StartAction::CONTINUE_TIME) {
                    $set['original_checkout_expire_at'] = new \Zend_Db_Expr('IF(`checkout_time` IS NOT NULL, UNIX_TIMESTAMP() + `checkout_time`, "' . $expireAt . '")');
                    $set['timer_expire_at'] = new \Zend_Db_Expr('`original_checkout_expire_at`');
                } else {
                    $set['original_checkout_expire_at'] = $expireAt;
                    $set['timer_expire_at'] = $expireAt;
                }

                // Save count of seconds that was on cart before move to checkout.
                $set['cart_time'] = new \Zend_Db_Expr('CAST(`original_cart_expire_at` AS SIGNED) - UNIX_TIMESTAMP()');
            } else {
                $set['timer_expire_at'] = $this->getGlobalTime();
            }
        }

        // Checkout -> Cart.
        if ($mode === Data::TIMER_MODE_CART) {
            if ($this->configHelper->getTimerMode() == TimerMode::SEPARATE) {
                $set['original_cart_expire_at'] = new \Zend_Db_Expr('UNIX_TIMESTAMP() + `cart_time`');
                $set['timer_expire_at'] = new \Zend_Db_Expr('`original_cart_expire_at`');

                // Save count of seconds that was on checkout before move to another page.
                $set['checkout_time'] =
                    new \Zend_Db_Expr('CAST(`original_checkout_expire_at` AS SIGNED) - UNIX_TIMESTAMP()');
            } else {
                $set['timer_expire_at'] = new \Zend_Db_Expr('`original_cart_expire_at`');
            }
        }

        if ($set) {
            $this->resource->getConnection()->update(
                $this->resource->getTableName('quote_item'),
                $set,
                [
                    'quote_id = ?' => $this->getQuoteId(),
                    'timer_expire_at NOT IN (?)' => $this->getReservationStatuses()
                ]
            );

            $this->updateGlobalTimer($this->getQuoteId());
        }

        return true;
    }

    /**
     * Check if global timer is enabled
     *
     * @return bool
     */
    public function globalTimerEnabled()
    {
        return $this->configHelper->getCartReservationType() == TimerType::TYPE_GLOBAL;
    }

    /**
     * Check if separate timer is enabled
     *
     * @return bool
     */
    public function separateTimerEnabled()
    {
        return $this->configHelper->getCartReservationType() == TimerType::TYPE_SEPARATE;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecomm\Invoice\Model\Filter;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;

/**
 * Class OrderNumber.
 *
 * Model for 'Order Number' filter for order search filter.
 */
class OrderNumber implements FilterInterface
{
    /**
     * @inheritdoc
     */
    public function applyFilter(Collection $ordersCollection, $value): Collection

    {
                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('text Hello');

        $ordersCollection->addFieldToFilter(OrderInterface::INCREMENT_ID, ['like' => '%' . $value . '%']);

        return $ordersCollection;
    }
}

<?php

declare(strict_types=1);

namespace Ecomm\Sap\Model\ResourceModel\SalesOrderPodExtension;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ecomm\Sap\Model\SalesOrderPodExtension as Model;
use Ecomm\Sap\Model\ResourceModel\SalesOrderPodExtension as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void// phpcs:ignore
    {
        $this->_init(
            Model::class,
            ResourceModel::class
        );
    }
}

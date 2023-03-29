<?php

declare(strict_types=1);

namespace Ecomm\Sap\Model;

use Magento\Framework\Model\AbstractModel;
use Ecomm\Sap\Model\ResourceModel\SalesOrderPodExtension as ResourceModel;

class SalesOrderPodExtension extends AbstractModel
{
    protected function _construct(): void// phpcs:ignore
    {
        $this->_init(ResourceModel::class);
    }
}

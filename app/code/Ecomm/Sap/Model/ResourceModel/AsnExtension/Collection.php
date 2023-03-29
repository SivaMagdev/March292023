<?php

declare(strict_types=1);

namespace Ecomm\Sap\Model\ResourceModel\AsnExtension;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ecomm\Sap\Model\AsnExtension as Model;
use Ecomm\Sap\Model\ResourceModel\AsnExtension as ResourceModel;

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

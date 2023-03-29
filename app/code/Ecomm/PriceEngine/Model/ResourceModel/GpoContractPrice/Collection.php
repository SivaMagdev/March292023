<?php

declare(strict_types=1);

namespace Ecomm\PriceEngine\Model\ResourceModel\GpoContractPrice;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ecomm\PriceEngine\Model\GpoContractPrice as Model;
use Ecomm\PriceEngine\Model\ResourceModel\GpoContractPrice as ResourceModel;

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

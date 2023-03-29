<?php

declare(strict_types=1);

namespace Ecomm\PriceEngine\Model;

use Magento\Framework\Model\AbstractModel;
use Ecomm\PriceEngine\Model\ResourceModel\GpoContractPrice as ResourceModel;

class GpoContractPrice extends AbstractModel
{
    protected function _construct(): void// phpcs:ignore
    {
        $this->_init(ResourceModel::class);
    }
}

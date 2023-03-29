<?php

declare(strict_types=1);

namespace Ecomm\PriceEngine\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class GpoContractPrice extends AbstractDb
{
    /** @var string Main table name */
    private const MAIN_TABLE = 'ecomm_gpo_contract_price';

    /** @var string Main table primary key field name */
    private const ID_FIELD_NAME = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void// phpcs:ignore
    {
        $this->_init(self::MAIN_TABLE, self::ID_FIELD_NAME);
    }
}

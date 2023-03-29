<?php

namespace Ecomm\Servicerequest\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{

	public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
		$installer = $setup;
		$installer->startSetup();
		if (version_compare($context->getVersion(), "1.0.0", "<")) {
        //Your upgrade script
        }

        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            if (!$installer->tableExists('ecomm_request_type')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('ecomm_request_type')
                )
                    ->addColumn(
                        'id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'nullable' => false,
                            'primary'  => true,
                            'unsigned' => true,
                        ],
                        'Id'
                    )
                    ->addColumn(
                        'type_name',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        254,
                        ['nullable => false', 'default' => 0],
                        'Type Name'
                    )
                    ->addColumn(
                        'created_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        255,
                        ['nullable => false'],
                        'Creation Date'
                    )
                    ->setComment('Service Request types');
                $installer->getConnection()->createTable($table);

                $installer->getConnection()->addIndex(
                    $installer->getTable('ecomm_request_type'),
                    $setup->getIdxName(
                        $installer->getTable('ecomm_request_type'),
                        ['type_name'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['type_name'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                );
            }

            if (!$installer->tableExists('ecomm_invoice_status')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('ecomm_invoice_status')
                )
                    ->addColumn(
                        'id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'nullable' => false,
                            'primary'  => true,
                            'unsigned' => true,
                        ],
                        'Id'
                    )
                    ->addColumn(
                        'status_name',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        254,
                        ['nullable => false', 'default' => 0],
                        'Status Name'
                    )
                    ->addColumn(
                        'created_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        255,
                        ['nullable => false'],
                        'Creation Date'
                    )
                    ->setComment('Invoice status names');
                $installer->getConnection()->createTable($table);

                $installer->getConnection()->addIndex(
                    $installer->getTable('ecomm_invoice_status'),
                    $setup->getIdxName(
                        $installer->getTable('ecomm_invoice_status'),
                        ['status_name'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['status_name'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                );
            }
        }

		$installer->endSetup();
	}
}
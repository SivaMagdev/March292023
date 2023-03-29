<?php

namespace Ecomm\PriceEngine\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('ecomm_gpo_price')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('ecomm_gpo_price')
			)
				->addColumn(
					'gpo_price_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'Price ID'
				)
				->addColumn(
					'product_sku',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Product SKU'
				)
				->addColumn(
					'ndc',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'NDC'
				)
				->addColumn(
					'name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Generic Name'
				)
				->addColumn(
					'strength_count',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Strength and Size/Count'
				)
				->addColumn(
					'pack_size',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Pack Size'
				)
				->addColumn(
	                'gpo_price',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
	                '12,2',
	                ['nullable' => false],
	                'Price'
	            )
				->addColumn(
	                'dish_price',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
	                '12,2',
	                ['nullable' => false],
	                'Price'
	            )
				->addColumn(
	                'direct_price',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
	                '12,2',
	                ['nullable' => false],
	                'Price'
	            )
				->addColumn(
	                'contract_ref',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                254,
	                ['nullable' => false],
	                '# Contract Ref'
	            )
				->addColumn(
	                'start_date',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
	                null,
	                [],
	                'Start Date'
	            )
				->addColumn(
	                'end_date',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
	                null,
	                [],
	                'End Date'
	            )
				->addColumn(
	                'gpo_name',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                254,
	                ['nullable' => false],
	                'GPO Name'
	            )
				->addColumn(
					'gpo_ref',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'GPO Ref#'
				)
				->addColumn(
					'deleted',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					1,
					[],
					'Deleted status'
				)
				->addColumn(
					'created_by',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Created Username'
				)
				->addColumn(
					'created_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Created At'
				)->addColumn(
					'updated_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
					'Updated At')
				->setComment('GPO Price list Table');
			$installer->getConnection()->createTable($table);

			$installer->getConnection()->addIndex(
				$installer->getTable('ecomm_gpo_price'),
				$setup->getIdxName(
					$installer->getTable('ecomm_gpo_price'),
					['product_sku'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['product_sku'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);
			$installer->getConnection()->addIndex(
				$installer->getTable('ecomm_gpo_price'),
				$setup->getIdxName(
					$installer->getTable('ecomm_gpo_price'),
					['name'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['name'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);
		}

		if (!$installer->tableExists('ecomm_exclusive_price')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('ecomm_exclusive_price')
			)
				->addColumn(
					'exclusive_price_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'Price ID'
				)
				->addColumn(
					'product_sku',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Product SKU'
				)
				->addColumn(
					'ndc',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'NDC'
				)
				->addColumn(
					'name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Generic Name'
				)
				->addColumn(
					'strength_count',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Strength and Size/Count'
				)
				->addColumn(
					'pack_size',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Pack Size'
				)
				->addColumn(
	                'customer_id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                254,
	                ['nullable' => false],
	                'SAP Customer ID'
	            )
				->addColumn(
	                'price',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
	                '12,2',
	                ['nullable' => false],
	                'Price'
	            )
				->addColumn(
	                'start_date',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
	                null,
	                [],
	                'Start Date'
	            )
				->addColumn(
	                'end_date',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
	                null,
	                [],
	                'End Date'
	            )
				->addColumn(
					'contract_ref',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Contract Ref#'
				)
				->addColumn(
					'deleted',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					1,
					[],
					'Deleted status'
				)
				->addColumn(
					'created_by',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Created Username'
				)
				->addColumn(
					'created_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Created At'
				)->addColumn(
					'updated_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
					'Updated At')
				->setComment('Contract Price list Table');
			$installer->getConnection()->createTable($table);

			$installer->getConnection()->addIndex(
				$installer->getTable('ecomm_exclusive_price'),
				$setup->getIdxName(
					$installer->getTable('ecomm_exclusive_price'),
					['product_sku'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['product_sku'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);
			$installer->getConnection()->addIndex(
				$installer->getTable('ecomm_exclusive_price'),
				$setup->getIdxName(
					$installer->getTable('ecomm_exclusive_price'),
					['name'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['name'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);
		}

		if (!$installer->tableExists('ecomm_shortdated_price')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('ecomm_shortdated_price')
			)
				->addColumn(
					'shortdated_price_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'Price ID'
				)
				->addColumn(
					'product_sku',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Product SKU'
				)
				->addColumn(
					'ndc',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'NDC'
				)
				->addColumn(
					'name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Generic Name'
				)
				->addColumn(
					'strength_count',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Strength and Size/Count'
				)
				->addColumn(
					'pack_size',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Pack Size'
				)
				->addColumn(
	                'shortdated_price',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
	                '12,2',
	                ['nullable' => false],
	                'Short Dated Price'
	            )
				->addColumn(
	                'inventory',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                5,
	                ['nullable' => false],
	                'Premier Price'
	            )
				->addColumn(
					'batch',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Bactch #'
				)
				->addColumn(
	                'expiry_date',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
	                null,
	                [],
	                'Expiry Date'
	            )
				->addColumn(
	                'start_date',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
	                null,
	                [],
	                'Start Date'
	            )
				->addColumn(
	                'end_date',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
	                null,
	                [],
	                'End Date'
	            )
				->addColumn(
					'deleted',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					1,
					[],
					'Deleted status'
				)
				->addColumn(
					'created_by',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Created Username'
				)
				->addColumn(
					'created_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Created At'
				)->addColumn(
					'updated_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
					'Updated At')
				->setComment('Short dated Price list Table');
			$installer->getConnection()->createTable($table);

			$installer->getConnection()->addIndex(
				$installer->getTable('ecomm_shortdated_price'),
				$setup->getIdxName(
					$installer->getTable('ecomm_shortdated_price'),
					['product_sku'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['product_sku'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);
			$installer->getConnection()->addIndex(
				$installer->getTable('ecomm_shortdated_price'),
				$setup->getIdxName(
					$installer->getTable('ecomm_shortdated_price'),
					['name'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['name'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);
		}

		if (!$installer->tableExists('ecomm_stock')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('ecomm_stock')
			)
				->addColumn(
					'stock_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'Stock ID'
				)
				->addColumn(
					'product_sku',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Product SKU'
				)
				->addColumn(
					'stock',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					5,
					['nullable => false'],
					'Stock Quantity'
				)
				->addColumn(
					'thereshold',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					5,
					['nullable => false'],
					'Stock Out Thereshold'
				)
				->addColumn(
	                'start_date',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
	                null,
	                [],
	                'Start Date'
	            )
				->addColumn(
	                'end_date',
	                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
	                null,
	                [],
	                'End Date'
	            )
				->addColumn(
					'deleted',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					1,
					[],
					'Deleted status'
				)
				->addColumn(
					'created_by',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					254,
					['nullable => false'],
					'Created Username'
				)
				->addColumn(
					'created_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Created At'
				)->addColumn(
					'updated_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
					'Updated At')
				->setComment('Stock list Table');
			$installer->getConnection()->createTable($table);

			$installer->getConnection()->addIndex(
				$installer->getTable('ecomm_stock'),
				$setup->getIdxName(
					$installer->getTable('ecomm_stock'),
					['product_sku'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['product_sku'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);
		}

		$installer->endSetup();
	}
}
<?php

namespace Ecomm\Servicerequest\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
		$installer = $setup;
		$installer->startSetup();

		/**
		 * Creating table service request
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('ecomm_service_request')
		)->addColumn(
			'id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Request Id'
		)->addColumn(
			'customer_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			11,
			['nullable => false', 'default' => 0],
			'Customer Id'
		)->addColumn(
			'request_type',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			11,
			['nullable => false', 'default' => 0],
			'Request Type'
		)->addColumn(
			'reference_number',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true,'default' => null],
			'Reference Number'
		)->addColumn(
			'request_description',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			'2M',
			['nullable' => true,'default' => null],
			'Request Description'
		)->addColumn(
			'attachment',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			'2M',
			['nullable' => true,'default' => null],
			'Attachment'
		)->addColumn(
			'status',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			2,
			['nullable' => false,'default' => 0],
			'request status'
		)->addColumn(
			'solution_description',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			'2M',
			['nullable' => true,'default' => null],
			'Solution Description'
		)->addColumn(
			'solution_attachment',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			'2M',
			['nullable' => true,'default' => null],
			'Solution Attachment'
		)->addColumn(
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
			'Updated At'
		)->setComment(
            'Service Requests'
        );
		$installer->getConnection()->createTable($table);
		$installer->endSetup();
	}
}
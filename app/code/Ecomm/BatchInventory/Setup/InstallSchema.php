<?php

namespace Ecomm\BatchInventory\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();

		$connection = $installer->getConnection();
        $connection->addColumn(
            $installer->getTable('catalog_product_option_type_value'),
            'quantity',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 4,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Quantity'
            ]
        );
        $connection->addColumn(
            $installer->getTable('catalog_product_option_type_value'),
            'expiry_date',
            [
                'type' => Table::TYPE_DATE,
                'nullable' => true,
                'comment' => 'Expiry Date'
            ]
        );

		$installer->endSetup();
	}
}
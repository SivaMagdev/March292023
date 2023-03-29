<?php

namespace Ecomm\BellNotification\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{

	public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
		$installer = $setup;
		$installer->startSetup();
        $true = true;
		if (version_compare($context->getVersion(), "1.1", "<")) {
        //Your upgrade script
        }
        if (version_compare($context->getVersion(), '1.2', '<')) {

            $pushNotificationTableName = $setup->getTable ( 'ecomm_push_notification' );
            if ($setup->getConnection ()->isTableExists ( $pushNotificationTableName ) != $true) {
                $pushNotificationTableName = $setup->getConnection()->newTable($pushNotificationTableName)
                    ->addColumn('id', Table::TYPE_INTEGER, null,['identity' => true,'unsigned' => true, 'nullable' => false, 'primary' => true], 'ID' )
                    ->addColumn('customer_id', Table::TYPE_INTEGER, null,['nullable' => false], 'Customer Id' )
                    ->addColumn('device_token', Table::TYPE_TEXT, null,['nullable' => false,'default' => ''], 'Device Token' )
                    ->addColumn('device_type', Table::TYPE_TEXT, null,['nullable' => false,'default' => ''], 'Device Type' )
                    ->addColumn('status', Table::TYPE_SMALLINT, null,['nullable' => false], 'Status' )
                    ->addColumn('created_at', Table::TYPE_TIMESTAMP, null,['nullable' => false,'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],'Created At' )
                    ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null,['nullable' => false ,'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],'Updated At')
                    ->setComment('Push Notification')
                    ->setOption ( 'type', 'InnoDB' )
                    ->setOption ( 'charset', 'utf8' );
                $setup->getConnection ()->createTable ( $pushNotificationTableName );
            }
        }

		$installer->endSetup();
	}
}
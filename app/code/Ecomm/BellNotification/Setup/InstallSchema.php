<?php

namespace Ecomm\BellNotification\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
class InstallSchema implements InstallSchemaInterface {
    /**
     * (non-PHPdoc)
     * 
     * @see \Ecomm\BellNotification\Setup\InstallSchemaInterface::install()
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup ();
        $true = true;

        $bellNotificationTableName = $setup->getTable ( 'ecomm_bell_notification' );
        if ($setup->getConnection ()->isTableExists ( $bellNotificationTableName ) != $true) {
            $bellNotificationTableName = $setup->getConnection()->newTable($bellNotificationTableName)
                ->addColumn('id', Table::TYPE_INTEGER, null,['identity' => true,'unsigned' => true, 'nullable' => false, 'primary' => true], 'ID' )
                ->addColumn('customer_id', Table::TYPE_INTEGER, null,['nullable' => false], 'Customer Id' )
                ->addColumn('customer_type', Table::TYPE_TEXT, null,['nullable' => false,'default' => ''], 'Customer Type' )
                ->addColumn('type_id', Table::TYPE_INTEGER, null,['nullable' => false], 'Type ID' )
                ->addColumn('type', Table::TYPE_TEXT, null,['nullable' => false,'default' => ''], 'Type' )
                 ->addColumn('assigned_user_id', Table::TYPE_INTEGER, null,['nullable' => false], 'Action Done By' )
                ->addColumn('comment', Table::TYPE_TEXT, 255,['nullable' => false,'default' => ''], 'Comments' )
                ->addColumn('status', Table::TYPE_SMALLINT, null,['nullable' => false], 'Status' )
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null,['nullable' => false,'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],'Created At' )
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null,['nullable' => false ,'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],'Updated At')
                ->setComment('Bell Notification')
                ->setOption ( 'type', 'InnoDB' )
                ->setOption ( 'charset', 'utf8' );
            $setup->getConnection ()->createTable ( $bellNotificationTableName );
        }
        $installer->endSetup ();
    }
}
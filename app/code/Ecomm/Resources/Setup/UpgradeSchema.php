<?php

namespace Ecomm\Resources\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), "0.2.0", "<")) {
            $installer->getConnection()->addColumn(
                $installer->getTable('ecomm_resources'),
                'hide_leave_popup',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    [],
                    'comment' => 'Remove leave popup'
                ]
            );
        }


        $installer->endSetup();
    }
}
<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 *
 * ==============================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * ==============================================================
 * This package designed for Magento COMMUNITY edition
 * PWC does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PWC does not provide extension support in case of
 * incorrect edition usage.
 * ==============================================================
 *
 * @category    PWC
 * @package     Ecomm_Resources
 * @version     1.2
 * @author      PWC Team
 *
 */

namespace Ecomm\Resources\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
class InstallSchema implements InstallSchemaInterface {
    /**
     * (non-PHPdoc)
     * 
     * @see \Colan\Distributors\Setup\InstallSchemaInterface::install()
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup ();
        $true = true;

        $resourcesTableName = $installer->getTable ( 'ecomm_resources_category' );        
        if ($installer->getConnection ()->isTableExists ( $resourcesTableName ) != $true) {
            $resourcesTable = $installer->getConnection ()->newTable ( $resourcesTableName )
            ->addColumn ( 'id', Table::TYPE_INTEGER, null, ['identity' => true,'unsigned' => true,'nullable' => false,'primary' => true], 'ID' )
            ->addColumn ( 'category', Table::TYPE_TEXT, null, ['nullable' => false,'default' => ''], 'Category' )
            ->addColumn ( 'status', Table::TYPE_SMALLINT, null, ['nullable' => false], 'Status' )
            ->addColumn ( 'created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT], 'Created At' )
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null,['nullable' => false ,'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],'Updated At')
            ->setComment ( 'Resources Category' )->setOption ( 'type', 'InnoDB' )->setOption ( 'charset', 'utf8' );
            $installer->getConnection ()->createTable ( $resourcesTable );

            $installer->getConnection()->addIndex(
                $installer->getTable('ecomm_resources_category'),
                $setup->getIdxName(
                    $installer->getTable('ecomm_resources_category'),
                    ['category'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['category'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        
        $resourcesTableName = $installer->getTable ( 'ecomm_resources' );        
        if ($installer->getConnection ()->isTableExists ( $resourcesTableName ) != $true) {
            $resourcesTable = $installer->getConnection ()->newTable ( $resourcesTableName )
            ->addColumn ( 'id', Table::TYPE_INTEGER, null, ['identity' => true,'unsigned' => true,'nullable' => false,'primary' => true], 'ID' )
            ->addColumn ( 'title', Table::TYPE_TEXT, null, ['nullable' => false,'default' => ''], 'Title' )
            ->addColumn ( 'category_id', Table::TYPE_TEXT, null, ['nullable' => false,'default' => ''], 'Category' )
            ->addColumn ( 'description', Table::TYPE_TEXT, null, ['nullable' => false,'default' => ''], 'Description' )
            ->addColumn ( 'link', Table::TYPE_TEXT, null, ['nullable' => false,'default' => ''], 'Link' )
            ->addColumn ( 'attachment', Table::TYPE_TEXT, null, ['nullable' => false,'default' => '' ], 'Attachment' )
            ->addColumn ( 'status', Table::TYPE_SMALLINT, null, ['nullable' => false], 'Status' )
            ->addColumn ( 'created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT], 'Created At' )
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null,['nullable' => false ,'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],'Updated At')
            ->setComment ( 'Resources' )->setOption ( 'type', 'InnoDB' )->setOption ( 'charset', 'utf8' );
            $installer->getConnection ()->createTable ( $resourcesTable );

            $installer->getConnection()->addIndex(
                $installer->getTable('ecomm_resources'),
                $setup->getIdxName(
                    $installer->getTable('ecomm_resources'),
                    ['title'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['title'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        
        $installer->endSetup ();
    }
}
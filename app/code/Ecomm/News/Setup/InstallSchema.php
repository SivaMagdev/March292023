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
 * @package     Ecomm_News
 * @version     1.2
 * @author      PWC Team
 *
 */

namespace Ecomm\News\Setup;

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
        
        $newsTableName = $installer->getTable ( 'ecomm_news' );        
        if ($installer->getConnection ()->isTableExists ( $newsTableName ) != $true) {
            $newsTable = $installer->getConnection ()->newTable ( $newsTableName )
            ->addColumn ( 'id', Table::TYPE_INTEGER, null, ['identity' => true,'unsigned' => true,'nullable' => false,'primary' => true], 'ID' )
            ->addColumn ( 'title', Table::TYPE_TEXT, null, ['nullable' => false,'default' => ''], 'Title' )
            ->addColumn ( 'description', Table::TYPE_TEXT, null, ['nullable' => false,'default' => ''], 'Description' )
            ->addColumn ( 'news_image', Table::TYPE_TEXT, null, ['nullable' => false,'default' => '' ], 'News Image' )
            ->addColumn ( 'status', Table::TYPE_SMALLINT, null, ['nullable' => false], 'Status' )
            ->addColumn ( 'published_at', Table::TYPE_TIMESTAMP, null, ['nullable' => true], 'Published At' )
            ->addColumn ( 'created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT], 'Created At' )
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null,['nullable' => false ,'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],'Updated At')
            ->setComment ( 'News' )->setOption ( 'type', 'InnoDB' )->setOption ( 'charset', 'utf8' );
            $installer->getConnection ()->createTable ( $newsTable );

            $installer->getConnection()->addIndex(
                $installer->getTable('ecomm_news'),
                $setup->getIdxName(
                    $installer->getTable('ecomm_news'),
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
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
 * @package     Ecomm_Supportdocument
 * @version     1.2
 * @author      PWC Team
 *
 */

namespace Ecomm\Supportdocument\Setup;

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
        
        $supportdocumentTableName = $installer->getTable ( 'ecomm_supportdocument' );        
        if ($installer->getConnection ()->isTableExists ( $supportdocumentTableName ) != $true) {
            $supportdocumentTable = $installer->getConnection ()->newTable ( $supportdocumentTableName )
            ->addColumn ( 'id', Table::TYPE_INTEGER, null, ['identity' => true,'unsigned' => true,'nullable' => false,'primary' => true], 'ID' )
            ->addColumn ( 'product_id', Table::TYPE_TEXT, null, ['nullable' => false,'default' => ''], 'Product Id' )
            ->addColumn ( 'link_title', Table::TYPE_TEXT, null, ['nullable' => false,'default' => ''], 'Link Title' )
            ->addColumn ( 'attachment', Table::TYPE_TEXT, null, ['nullable' => false,'default' => ''], 'Attachment' )
            ->addColumn ( 'link', Table::TYPE_TEXT, null, ['nullable' => false,'default' => ''], 'Link' )
            ->addColumn ( 'is_logged_in', Table::TYPE_SMALLINT, null, ['nullable' => false], 'Is Logged In' )
            ->addColumn ( 'status', Table::TYPE_SMALLINT, null, ['nullable' => false], 'Status' )
            ->addColumn ( 'created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT], 'Created At' )
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null,['nullable' => false ,'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],'Updated At')
            ->setComment ( 'Support Document' )->setOption ( 'type', 'InnoDB' )->setOption ( 'charset', 'utf8' );
            $installer->getConnection ()->createTable ( $supportdocumentTable );

            $installer->getConnection()->addIndex(
                $installer->getTable('ecomm_supportdocument'),
                $setup->getIdxName(
                    $installer->getTable('ecomm_supportdocument'),
                    ['product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['product_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        
        $installer->endSetup ();
    }
}
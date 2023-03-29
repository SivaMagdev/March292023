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
 * @package     Ecomm_Rewards
 * @version     1.2
 * @author      PWC Team
 *
 */

namespace Ecomm\Rewards\Setup;

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
        
        $rewardsTableName = $installer->getTable ( 'ecomm_rewards_customer' );        
        if ($installer->getConnection ()->isTableExists ( $rewardsTableName ) != $true) {
            $rewardsTable = $installer->getConnection ()->newTable ( $rewardsTableName )
            ->addColumn ( 'id', Table::TYPE_INTEGER, null, ['identity' => true,'unsigned' => true,'nullable' => false,'primary' => true], 'ID' )
            ->addColumn ( 'title', Table::TYPE_TEXT, null, ['nullable' => false,'default' => ''], 'Title' )
            ->addColumn ( 'description', Table::TYPE_TEXT, null, ['nullable' => false,'default' => ''], 'Description' )
            ->addColumn ( 'rewards_image', Table::TYPE_TEXT, null, ['nullable' => false,'default' => '' ], 'Rewards Image' )
            ->addColumn ( 'status', Table::TYPE_SMALLINT, null, ['nullable' => false], 'Status' )
            ->addColumn ( 'created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT], 'Created At' )
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null,['nullable' => false ,'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],'Updated At')
            ->setComment ( 'Rewards' )->setOption ( 'type', 'InnoDB' )->setOption ( 'charset', 'utf8' );
            $installer->getConnection ()->createTable ( $rewardsTable );

            $installer->getConnection()->addIndex(
                $installer->getTable('ecomm_rewards_customer'),
                $setup->getIdxName(
                    $installer->getTable('ecomm_rewards_customer'),
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
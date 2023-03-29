<?php

namespace Ecomm\Sap\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order_grid'),
                'sap_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    254,
                    ['nullable' => false],
                    'comment' => 'IDOC document number'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'sap_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    254,
                    ['nullable' => false],
                    'comment' => 'IDOC document number'
                ]
            );

            if (!$installer->tableExists('ecomm_sap_order_ack')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('ecomm_sap_order_ack')
                )
                    ->addColumn(
                        'id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'nullable' => false,
                            'primary'  => true,
                            'unsigned' => true,
                        ],
                        'Id'
                    )
                    ->addColumn(
                        'order_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        254,
                        ['nullable => false', 'default' => 0],
                        'Magento Id'
                    )
                    ->addColumn(
                        'sap_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable => false'],
                        'Status'
                    )
                    ->addColumn(
                        'created_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        255,
                        ['nullable => false'],
                        'Creation Date'
                    )
                    ->addColumn(
                        'ack_info',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        null,
                        ['nullable => false'],
                        'ACK json info'
                    )
                    ->setComment('SAP Order ACK Table');
                $installer->getConnection()->createTable($table);

                $installer->getConnection()->addIndex(
                    $installer->getTable('ecomm_sap_order_ack'),
                    $setup->getIdxName(
                        $installer->getTable('ecomm_sap_order_ack'),
                        ['order_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['order_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                );
            }
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order_grid'),
                'batch_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    254,
                    ['nullable' => false],
                    'comment' => 'Batch number'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'batch_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    254,
                    ['nullable' => false],
                    'comment' => 'Batch number'
                ]
            );

            if (!$installer->tableExists('ecomm_sap_order_asn')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('ecomm_sap_order_asn')
                )
                    ->addColumn(
                        'id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'nullable' => false,
                            'primary'  => true,
                            'unsigned' => true,
                        ],
                        'Id'
                    )
                    ->addColumn(
                        'sap_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable => false'],
                        'SAP ID'
                    )
                    ->addColumn(
                        'delivery_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable => false'],
                        'Delivery Number'
                    )
                    ->addColumn(
                        'created_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        255,
                        ['nullable => false'],
                        'Creation Date'
                    )
                    ->addColumn(
                        'asn_info',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        null,
                        ['nullable => false'],
                        'ACK json info'
                    )
                    ->setComment('SAP Order ASN Table');
                $installer->getConnection()->createTable($table);

                $installer->getConnection()->addIndex(
                    $installer->getTable('ecomm_sap_order_asn'),
                    $setup->getIdxName(
                        $installer->getTable('ecomm_sap_order_asn'),
                        ['sap_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['sap_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                );
            }
            if (!$installer->tableExists('ecomm_sap_order_invoice')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('ecomm_sap_order_invoice')
                )
                    ->addColumn(
                        'id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'nullable' => false,
                            'primary'  => true,
                            'unsigned' => true,
                        ],
                        'Id'
                    )
                    ->addColumn(
                        'sap_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable => false'],
                        'SAP ID'
                    )
                    ->addColumn(
                        'invoice_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable => false'],
                        'Invoice Number'
                    )
                    ->addColumn(
                        'created_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        255,
                        ['nullable => false'],
                        'Creation Date'
                    )
                    ->addColumn(
                        'invoice_info',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        null,
                        ['nullable => false'],
                        'Invoice json info'
                    )
                    ->setComment('SAP Order ACK Table');
                $installer->getConnection()->createTable($table);

                $installer->getConnection()->addIndex(
                    $installer->getTable('ecomm_sap_order_invoice'),
                    $setup->getIdxName(
                        $installer->getTable('ecomm_sap_order_invoice'),
                        ['sap_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['sap_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                );
            }
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {

            if (!$installer->tableExists('ecomm_sap_order_asnprint')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('ecomm_sap_order_asnprint')
                )
                    ->addColumn(
                        'id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'nullable' => false,
                            'primary'  => true,
                            'unsigned' => true,
                        ],
                        'Id'
                    )
                    ->addColumn(
                        'delivery_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable => false'],
                        'Delivery Number'
                    )
                    ->addColumn(
                        'created_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        255,
                        ['nullable => false'],
                        'Creation Date'
                    )
                    ->addColumn(
                        'asn_info',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        null,
                        ['nullable => false'],
                        'ACK Print json info'
                    )
                    ->setComment('SAP Order ASN Print Table');
                $installer->getConnection()->createTable($table);

                $installer->getConnection()->addIndex(
                    $installer->getTable('ecomm_sap_order_asnprint'),
                    $setup->getIdxName(
                        $installer->getTable('ecomm_sap_order_asnprint'),
                        ['delivery_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['delivery_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                );
            }

            if (!$installer->tableExists('ecomm_sap_order_cancel')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('ecomm_sap_order_cancel')
                )
                    ->addColumn(
                        'id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'nullable' => false,
                            'primary'  => true,
                            'unsigned' => true,
                        ],
                        'Id'
                    )
                    ->addColumn(
                        'order_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        254,
                        ['nullable => false', 'default' => 0],
                        'Magento Id'
                    )
                    ->addColumn(
                        'sap_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable => false'],
                        'Sales Order ID'
                    )
                    ->addColumn(
                        'created_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        255,
                        ['nullable => false'],
                        'Creation Date'
                    )
                    ->addColumn(
                        'cancel_info',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        null,
                        ['nullable => false'],
                        'Cancel json info'
                    )
                    ->setComment('SAP Order Cancel Table');
                $installer->getConnection()->createTable($table);

                $installer->getConnection()->addIndex(
                    $installer->getTable('ecomm_sap_order_cancel'),
                    $setup->getIdxName(
                        $installer->getTable('ecomm_sap_order_cancel'),
                        ['sap_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['sap_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                );
            }
        }
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('ecomm_sap_order_asn'),
                'rdd_date',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    [],
                    'comment' => 'RDD Date'
                ]
            );
        }

        $installer->endSetup();
    }
}

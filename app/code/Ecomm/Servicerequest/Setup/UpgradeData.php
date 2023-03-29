<?php
namespace Ecomm\Servicerequest\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeData implements UpgradeDataInterface
{

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $conn = $setup->getConnection();

        $tableRequestType = $setup->getTable('ecomm_request_type');
        $tableInvoiceStatus = $setup->getTable('ecomm_invoice_status');

        if (version_compare($context->getVersion(), '1.0.1', '<')) {


            $dataRequestType = [
                [
                    'type_name' => 'General Inquiries'
                ],
                [
                    'type_name' => 'Product Complaints'
                ],
                [
                    'type_name' => 'Product Inquiries'
                ],
                [
                    'type_name' => 'Upcoming Order'
                ],
                [
                    'type_name' => 'Returns / Cancellation'
                ],
                [
                    'type_name' => 'Damage / Shortages'
                ],
                [
                    'type_name' => 'Profile Update'
                ]
            ];
            $conn->insertMultiple($tableRequestType, $dataRequestType);

            $dataInvoiceStatus = [
                [
                    'status_name' => 'Open'
                ],
                [
                    'status_name' => 'Paid'
                ],
                [
                    'status_name' => 'Canceled'
                ]
            ];
            $conn->insertMultiple($tableInvoiceStatus, $dataInvoiceStatus);

        }

        $setup->endSetup();
    }
}
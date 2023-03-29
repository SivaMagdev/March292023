<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecomm\Theme\Plugin;

use Magento\Framework\Data\Collection;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class CollectionPool
 */
class GridCustomerAddressJoinCollection
{

    public static $table = 'customer_adress_entity';
    public static $leftJoinTable = 'sales_order'; // My custom table
    /**
     * Get report collection
     *
     * @param string $requestName
     * @return Collection
     * @throws \Exception
     */
    public function afterGetReport(
     \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
     $collection,
     $requestName
 ) {
       //echo 'requestName: '.$requestName;
        if($requestName == 'customer_address_listing')
        {
             if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {

                $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);

                $collection
                    ->getSelect()
                    ->joinLeft(
                        ['co'=>$leftJoinTableName],
                        "co.customer_id = main_table.entity_id",
                        [
                            'customer_id' => 'co.customer_id',
                            'custom_filed'=> 'co.custom_filed'
                        ]
                    );
                    //return data with left join customer_id from sales_order and custom_filed

                $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);

                $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where)->group('main_table.entity_id');;

                //echo $collection->getSelect()->__toString();die;

            }
        }
        return $collection;
    }
}
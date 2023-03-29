<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecomm\Globaldeclaration\Cron;

use Magento\Framework\App\ResourceConnection;

class PriceDecreaseNotification
{

    protected $logger;
    protected $globalhelper;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Ecomm\Globaldeclaration\Helper\Globalhelper $globalhelper,
        ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->globalhelper = $globalhelper;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        try{
            $connection = $this->resourceConnection->getConnection();
            $query = "select 
            customer_id
            from ecomm_price_decrease_notify
            WHERE notified='No'
            GROUP BY customer_id";
            // limit 0,2;";
            $result = $connection->fetchAll($query);
            foreach ($result as $key => $value) {
                $resultData = $this->globalhelper->customerPriceBellEmailNotification($value['customer_id']);
                $data = ["notified"=>"Yes"]; // Key_Value Pair
                $where = ['customer_id = ?' => (int)$value['customer_id']];
                $resultData = $connection->update("ecomm_price_decrease_notify", $data, $where);
            }
        }catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        $this->logger->addInfo("Cronjob PriceDecreaseNotification is executed.");
    }
}


<?php
/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_HinCron
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\HinCron\Cron;

use Ecomm\HinValidator\Block\Adminhtml\Index\Runner;

class Cronfile
{
    protected $runner;

    public function __construct(Runner $runner){
        $this->runner = $runner;
    }

    public function execute()
    {
        $writer = new \Laminas\Log\Writer\Stream(BP . '/var/log/hincron.log');
        $logger = new  \Laminas\Log\Logger();
        $logger->addWriter($writer);

        $logger->info('HIN Cron Starts Here');
        $result  = $this->runner->getHinValidation();
        $logger->info('Result '. json_encode($result));
        $logger->info('HIN Cron Ends Here');
    }
}
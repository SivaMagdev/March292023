<?php
/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_GcpIntegration
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Ecomm\GcpIntegration\Cron;

use Psr\Log\LoggerInterface;

class ImportVistexPrice
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ImportService
     */
    protected $importService;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param Ecomm\GcpIntegration\Model\Service\ImportService $importService
     */
    public function __construct(
        LoggerInterface $logger,
        \Ecomm\GcpIntegration\Model\Service\ImportService $importService
    ) {
        $this->logger = $logger;
        $this->importService = $importService;
    }

    public function execute()
    {
        $this->logger->info('Vistex Price Import Starts here');
        $priceImport = $this->importService->processImport();
        $this->logger->info('Result: ' . json_encode($priceImport));
        $this->logger->info('Vistex Price Import Ends here');

    }
}
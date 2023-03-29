<?php

/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_GcpIntegration
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\GcpIntegration\Model;

use Google\Cloud\Storage\StorageClient;
use Magento\Framework\DataObject;
use Magento\Framework\File\Csv;
use Psr\Log\LoggerInterface;
use Ecomm\GcpIntegration\Model\Config;

class GcsClient extends DataObject
{
	/**
     * @var StorageClient
     */
    protected $storageClient;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Csv
     */
    private $csv;

    /**
     * @var LoggerInterface
     */
    private $logger;

    protected $gcsClient;

	/**
     * Constructor
     *
     * @param Config $config
     * @param Csv $csv
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        Csv $csv,
        LoggerInterface $logger
    ) {
		parent::__construct();

        $this->config = $config;
        $this->csv = $csv;
        $this->logger = $logger;


        $this->gcsClient = new StorageClient([
            'keyFile' => json_decode($this->config->getGcsKey(), true),
            'projectId' => $this->config->getGcsProject()
        ]);
	}

    public function readFile($location)
    {
        $contents = '';
        $bucket = $this->gcsClient->bucket($this->config->getGcsBucketName());
        $this->gcsClient->registerStreamWrapper();
        try {
            $contents = $this->getCsvData($location);
        } catch (\Exception $e) {
            return false;
        }
        return $contents;
    }

    /**
     * To get CSV data
     *
     * @param string $file
     * @return array
     * @throws Exception
     */
    public function getCsvData($file)
    {
        $this->csv->setDelimiter(',');
        $result = $this->csv->getData($file);
        if (!is_array($result)) {
            $this->logger->error("File format is wrong, import failed.");
            throw new Exception(__("File format is wrong, please upload file in csv format."));
        }
        return $result;
    }
}

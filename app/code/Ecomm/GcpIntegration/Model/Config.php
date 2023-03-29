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

namespace Ecomm\GcpIntegration\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;


/**
 * Retrieve configuration for MasterImport
 */
class Config
{
    public const GCS_BUCKET_NAME = 'drl_gcs/general/gcs_bucket_name';
    public const GCS_LOCATION = 'drl_gcs/general/gcs_location';
    public const GCS_PROJECT = 'drl_gcs/general/gcs_project';
    public const GCS_KEY = 'drl_gcs/general/gcs_key';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * get bucket name
     *
     * @return string
     */
    public function getGcsBucketName()
    {
        return $this->scopeConfig->getValue(self::GCS_BUCKET_NAME);
    }

    /**
     * get location
     *
     * @return string
     */
    public function getGcsLocation()
    {
        return $this->scopeConfig->getValue(self::GCS_LOCATION);
    }

    /**
     * get project
     *
     * @return string
     */
    public function getGcsProject()
    {
        return $this->scopeConfig->getValue(self::GCS_PROJECT);
    }

    /**
     * Return auth key
     *
     * @return string
     */
    public function getGcsKey()
    {
        return $this->scopeConfig->getValue(self::GCS_KEY);
    }
}

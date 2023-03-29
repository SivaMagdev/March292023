<?php

namespace Ecomm\Api\Api\Data;

/**
 * @api
 */
interface VersionInfodataInterface
{

    /**
     * Get app_version
     *
     * @return string
     */
    public function getAppVersion();

      /**
     * Set app_version
     *
     * @param string $app_version
     * @return $this
     */
    public function setAppVersion($app_version);

    /**
     * Get tc_version
     *
     * @return string
     */
    public function getTcVersion();

      /**
     * Set tc_version
     *
     * @param string $tc_version
     * @return $this
     */
    public function setTcVersion($tc_version);

    /**
     * Get elau_version
     *
     * @return string
     */
    public function getEulaVersion();

      /**
     * Set elau_version
     *
     * @param string $elau_version
     * @return $this
     */
    public function setEulaVersion($elau_version);

}
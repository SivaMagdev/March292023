<?php

namespace Ecomm\Api\Model;

class VersionInfomodel extends \Magento\Framework\Model\AbstractModel implements
    \Ecomm\Api\Api\Data\VersionInfodataInterface
{
    const KEY_APP_VERSION = 'APP_VERSION';

    const KEY_TC_VERSION = 'TC_VERSION';

    const KEY_EULA_VERSION = 'EULA_VERSION';


     public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    public function getAppVersion()
    {
        return $this->_getData(self::KEY_APP_VERSION);
    }


    /**
     * Set app_version
     *
     * @param string $app_version
     * @return $this
     */
    public function setAppVersion($app_version)
    {
        return $this->setData(self::KEY_APP_VERSION, $app_version);
    }


    public function getTcVersion()
    {
        return $this->_getData(self::KEY_TC_VERSION);
    }


    /**
     * Set tc_version
     *
     * @param string $tc_version
     * @return $this
     */
    public function setTcVersion($tc_version)
    {
        return $this->setData(self::KEY_TC_VERSION, $tc_version);
    }


    public function getEulaVersion()
    {
        return $this->_getData(self::KEY_EULA_VERSION);
    }


    /**
     * Set elau_version
     *
     * @param string $elau_version
     * @return $this
     */
    public function setEulaVersion($elau_version)
    {
        return $this->setData(self::KEY_EULA_VERSION, $elau_version);
    }

}
<?php
namespace Ecomm\Api\Model;

use Ecomm\Api\Api\PolicyTrack\PolicyTrackInterface;

class PolicyTrack extends \Magento\Framework\Model\AbstractModel implements PolicyTrackInterface
{
	const CACHE_TAG = 'ecomm_app_policy_track';

	protected $_cacheTag = 'ecomm_app_policy_track';

	protected $_eventPrefix = 'ecomm_app_policy_track';

	protected function _construct()
	{
		$this->_init('Ecomm\Api\Model\ResourceModel\PolicyTrack');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}

	/**
     * Get Email
     *
     * @return string
     */
    public function getEmail()
    {
    	return $this->getData(PolicyTrackInterface::EMAIL);
    }

    /**
     * Set Email
     *
     * @param $email
     * @return mixed
     */
    public function setEmail($email)
    {
    	return $this->setData(PolicyTrackInterface::EMAIL, $email);
    }

    /**
     * Get TcVersion
     *
     * @return string
     */
    public function getTcVersion()
    {
    	return $this->getData(PolicyTrackInterface::TC_VERSION);
    }

    /**
     * Set TcVersion
     *
     * @param $tc_version
     * @return mixed
     */
    public function setTcVersion($tc_version)
    {
    	return $this->setData(PolicyTrackInterface::TC_VERSION, $tc_version);
    }

    /**
     * Get EulaVersion
     *
     * @return string
     */
    public function getEulaVersion()
    {
    	return $this->getData(PolicyTrackInterface::EULA_VERSION);
    }

    /**
     * Set EulaVersion
     *
     * @param $eula_version
     * @return mixed
     */
    public function setEulaVersion($eula_version)
    {
    	return $this->setData(PolicyTrackInterface::EULA_VERSION, $eula_version);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
    	return $this->getData(PolicyTrackInterface::CREATED_AT);
    }

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setCreatedAt($createdAt)
    {
    	return $this->setData(PolicyTrackInterface::CREATED_AT, $createdAt);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
    	return $this->getData(PolicyTrackInterface::UPDATED_AT);
    }

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return DataInterface
     */
    public function setUpdatedAt($updatedAt)
    {
    	return $this->setData(PolicyTrackInterface::UPDATED_AT, $updatedAt);
    }
}
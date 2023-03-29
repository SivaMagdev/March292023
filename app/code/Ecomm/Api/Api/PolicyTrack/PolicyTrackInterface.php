<?php

namespace Ecomm\Api\Api\PolicyTrack;

interface PolicyTrackInterface
{
   /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                    = 'id';
    const EMAIL                 = 'email';
    const TC_VERSION            = 'tc_version';
    const EULA_VERSION          = 'eula_version';
    const CREATED_AT            = 'created_at';
    const UPDATED_AT            = 'updated_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param $id
     * @return DataInterface
     */
    public function setId($id);

    /**
     * Get Email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set Email
     *
     * @param $email
     * @return string
     */
    public function setEmail($email);

    /**
     * Get TcVersion
     *
     * @return string
     */
    public function getTcVersion();

    /**
     * Set TcVersion
     *
     * @param $tc_version
     * @return mixed
     */
    public function setTcVersion($tc_version);

    /**
     * Get EulaVersion
     *
     * @return string
     */
    public function getEulaVersion();

    /**
     * Set EulaVersion
     *
     * @param $eula_version
     * @return mixed
     */
    public function setEulaVersion($eula_version);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return DataInterface
     */
    public function setUpdatedAt($updatedAt);
}

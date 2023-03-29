<?php

namespace Ecomm\ExclusivePrice\Api\Data;
interface ContractPriceInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'entity_id';
    const CONTRACT_ID = 'contract_id';
    const CONTRACT_TYPE = 'contract_type';
    const GPO_NAME = 'gpo_name';
    const IS_DSH = 'is_dsh';
    const STATUS = 'status';
    const START_DATE = 'start_date';
    const END_DATE = 'end_date';
    const DELETED = 'deleted';
    const CREATED_BY = 'created_by';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    // const CREATED_AT = 'updated_at';
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId();
    /**
     * Set EntityId.
     */
    public function setEntityId($entityId);
    /**
     * Get Title.
     *
     * @return varchar
     */
    public function getContractId();
    /**
     * Set Title.
     */
    public function setContractId($contractId);
    /**
     * Get Content.
     *
     * @return varchar
     */
    public function getContractType();
    /**
     * Set Content.
     */
    public function setContractType($contractType);
    /**
     * Get Publish Date.
     *
     * @return varchar
     */
    public function getGpoName();
    /**
     * Set PublishDate.
     */
    public function setGpoName($gpoName);
    /**
     * Get IsActive.
     *
     * @return varchar
     */
    public function getIsDsh();
    /**
     * Set StartingPrice.
     */
    public function setIsdsh($isDsh);
    /**
     * Get UpdateTime.
     *
     * @return varchar
     */
    public function getStatus();
    /**
     * Set UpdateTime.
     */
    public function setStatus($status);

    public function getStartDate();
    /**
     * Set UpdateTime.
     */
    public function setStartDate($startDate);

    public function getEndDate();
    /**
     * Set UpdateTime.
     */
    public function setEndDate($endDate);

    public function getDeleted();
    /**
     * Set UpdateTime.
     */
    public function setDeleted($deleted);

    public function getCreatedBy();
    /**
     * Set UpdateTime.
     */
    public function setCreatedBy($createdBy);

    public function getCreatedAt();
    /**
     * Set UpdateTime.
     */
    public function setCreatedAt($createdAt);

    public function getUpdatedAt();
    /**
     * Set UpdateTime.
     */
    public function setUpdatedAt($updatedAt);

    public function getName();
    /**
     * Set UpdateTime.
     */
    public function setName($name);
    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    // public function getCreatedAt();
    // /**
    //  * Set CreatedAt.
    //  */
    // public function setCreatedAt($createdAt);
}
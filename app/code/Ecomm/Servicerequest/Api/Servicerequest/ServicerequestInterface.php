<?php

namespace Ecomm\Servicerequest\Api\Servicerequest;

interface ServicerequestInterface
{
   /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                    = 'id';
    const CUSTOMER_ID           = 'customer_id';
    const REQUEST_TYPE          = 'request_type';
    const REFERENCE_NUMBER      = 'reference_number';
    const CONTENT               = 'content';
    const REQUEST_DESCRIPTION   = 'request_description';
    const ATTACHMENT            = 'attachment';
    const STATUS                = 'status';
    const SOLUTION_DESCRIPTION  = 'solution_description';
    const SOLUTION_ATTACHMENT   = 'solution_attachment';
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
     * Get CustomerId
     *
     * @return string
     */
    public function getCustomerId();

    /**
     * Set CustomerId
     *
     * @param $customer_id
     * @return string
     */
    public function setCustomerId($customer_id);

    /**
     * Get RequestType
     *
     * @return string
     */
    public function getRequestType();

    /**
     * Set RequestType
     *
     * @param $request_type
     * @return mixed
     */
    public function setRequestType($request_type);

    /**
     * Get ReferenceNumber
     *
     * @return string
     */
    public function getReferenceNumber();

    /**
     * Set ReferenceNumber
     *
     * @param $reference_number
     * @return mixed
     */
    public function setReferenceNumber($reference_number);

    /**
     * Set media gallery content
     *
     * @param $content \Magento\Framework\Api\Data\ImageContentInterface
     * @return $this
     */
    public function setContent($content);

    /**
     * @return \Magento\Framework\Api\Data\ImageContentInterface|null
     */
    public function getContent();

    /**
     * Get RequestDescription
     *
     * @return mixed
     */
    public function getRequestDescription();

    /**
     * Set RequestDescription
     *
     * @param $request_description
     * @return mixed
     */
    public function setRequestDescription($request_description);

    /**
     * Get Attachment
     *
     * @return mixed
     */
    public function getAttachment();

    /**
     * Set Attachment
     *
     * @param $attachment
     * @return mixed
     */
    public function setAttachment($attachment);

    /**
     * Get Status
     *
     * @return bool|int
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param $status
     * @return DataInterface
     */
    public function setStatus($status);

    /**
     * Get RequestDescription
     *
     * @return mixed
     */
    public function getSolutionDescription();

    /**
     * Set SolutionDescription
     *
     * @param $solution_description
     * @return mixed
     */
    public function setSolutionDescription($solution_description);

    /**
     * Get SolutionAttachment
     *
     * @return mixed
     */
    public function getSolutionAttachment();

    /**
     * Set SolutionAttachment
     *
     * @param $solution_attachment
     * @return mixed
     */
    public function setSolutionAttachment($solution_attachment);

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

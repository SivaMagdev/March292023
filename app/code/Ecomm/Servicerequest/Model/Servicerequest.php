<?php

namespace Ecomm\Servicerequest\Model;

use Magento\Framework\Model\AbstractModel;
use Ecomm\Servicerequest\Api\Servicerequest\ServicerequestInterface;

/**
 * This class initiates order model
 */
class Servicerequest extends AbstractModel implements ServicerequestInterface {
    /**
     * Define resource model
     */
    protected function _construct() {
        $this->_init ( 'Ecomm\Servicerequest\Model\ResourceModel\Servicerequest' );
    }

    /**
     * Get cache identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get CustomerId
     *
     * @return string
     */
    public function getCustomerId()
    {
    	return $this->getData(ServicerequestInterface::CUSTOMER_ID);
    }

    /**
     * Set CustomerId
     *
     * @param $customer_id
     * @return mixed
     */
    public function setCustomerId($customer_id)
    {
    	return $this->setData(ServicerequestInterface::CUSTOMER_ID, $customer_id);
    }

    /**
     * Get RequestType
     *
     * @return mixed
     */
    public function getRequestType()
    {
        return $this->getData(ServicerequestInterface::REQUEST_TYPE);
    }

    /**
     * Set RequestType
     *
     * @param $request_type
     * @return mixed
     */
    public function setRequestType($request_type)
    {
        return $this->setData(ServicerequestInterface::REQUEST_TYPE, $request_type);
    }

    /**
     * Get ReferenceNumber
     *
     * @return mixed
     */
    public function getReferenceNumber()
    {
        return $this->getData(ServicerequestInterface::REFERENCE_NUMBER);
    }

    /**
     * Set ReferenceNumber
     *
     * @param $reference_number
     * @return mixed
     */
    public function setReferenceNumber($reference_number)
    {
        return $this->setData(ServicerequestInterface::REFERENCE_NUMBER, $reference_number);
    }

    /**
     * Set media gallery content
     *
     * @param $content \Magento\Framework\Api\Data\ImageContentInterface
     * @return $this
     */
    public function setContent($content)
    {
        return $this->setData(ServicerequestInterface::CONTENT, $content);
    }

    /**
     * @return \Magento\Framework\Api\Data\ImageContentInterface|null
     */
    public function getContent()
    {
        return $this->getData(ServicerequestInterface::CONTENT);
    }

    /**
     * Get RequestDescription
     *
     * @return mixed
     */
    public function getRequestDescription()
    {
    	return $this->getData(ServicerequestInterface::REQUEST_DESCRIPTION);
    }

    /**
     * Set RequestDescription
     *
     * @param $request_description
     * @return mixed
     */
    public function setRequestDescription($request_description)
    {
    	return $this->setData(ServicerequestInterface::REQUEST_DESCRIPTION, $request_description);
    }

    /**
     * Get Attachment
     *
     * @return mixed
     */
    public function getAttachment()
    {
    	return $this->getData(ServicerequestInterface::ATTACHMENT);
    }

    /**
     * Set Attachment
     *
     * @param $attachment
     * @return mixed
     */
    public function setAttachment($attachment)
    {
    	return $this->setData(ServicerequestInterface::ATTACHMENT, $attachment);
    }

    /**
     * Get SolutionDescription
     *
     * @return mixed
     */
    public function getSolutionDescription()
    {
        return $this->getData(ServicerequestInterface::SOLUTION_DESCRIPTION);
    }

    /**
     * Set SolutionDescription
     *
     * @param $solution_description
     * @return mixed
     */
    public function setSolutionDescription($solution_description)
    {
        return $this->setData(ServicerequestInterface::SOLUTION_DESCRIPTION, $solution_description);
    }

    /**
     * Get SolutionAttachment
     *
     * @return mixed
     */
    public function getSolutionAttachment()
    {
        return $this->getData(ServicerequestInterface::SOLUTION_ATTACHMENT);
    }

    /**
     * Set SolutionAttachment
     *
     * @param $solution_attachment
     * @return mixed
     */
    public function setSolutionAttachment($solution_attachment)
    {
        return $this->setData(ServicerequestInterface::SOLUTION_ATTACHMENT, $solution_attachment);
    }

    /**
     * Get Status
     *
     * @return bool|int
     */
    public function getStatus()
    {
    	return $this->getData(ServicerequestInterface::STATUS);
    }

    /**
     * Set Status
     *
     * @param $status
     * @return DataInterface
     */
    public function setStatus($status)
    {
    	return $this->setData(ServicerequestInterface::STATUS, $status);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
    	return $this->getData(ServicerequestInterface::CREATED_AT);
    }

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setCreatedAt($createdAt)
    {
    	return $this->setData(ServicerequestInterface::CREATED_AT, $createdAt);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
    	return $this->getData(ServicerequestInterface::UPDATED_AT);
    }

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return DataInterface
     */
    public function setUpdatedAt($updatedAt)
    {
    	return $this->setData(ServicerequestInterface::UPDATED_AT, $updatedAt);
    }
}
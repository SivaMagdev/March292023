<?php

namespace Ecomm\Api\Model;

class AccessTokenmodel extends \Magento\Framework\Model\AbstractModel implements
    \Ecomm\Api\Api\Data\AccessTokendataInterface
{
    const KEY_ACCESS_TOKEN = 'ACCESS_TOKEN';

    const KEY_TOKEN_TYPE = 'TOKEN_TYPE';

    const KEY_ISSUED_AT = 'ISSUED_AT';

    const KEY_EXPIRES_IN = 'EXPIRES_IN';


     public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    public function getAccessToken()
    {
        return $this->_getData(self::KEY_ACCESS_TOKEN);
    }


    /**
     * Set access_token
     *
     * @param string $access_token
     * @return $this
     */
    public function setAccessToken($access_token)
    {
        return $this->setData(self::KEY_ACCESS_TOKEN, $access_token);
    }


    public function getTokenType()
    {
        return $this->_getData(self::KEY_TOKEN_TYPE);
    }


    /**
     * Set token_type
     *
     * @param string $token_type
     * @return $this
     */
    public function setTokenType($token_type)
    {
        return $this->setData(self::KEY_TOKEN_TYPE, $token_type);
    }


    public function getIssuedAt()
    {
        return $this->_getData(self::KEY_ISSUED_AT);
    }


    /**
     * Set issued_at
     *
     * @param string $issued_at
     * @return $this
     */
    public function setIssuedAt($issued_at)
    {
        return $this->setData(self::KEY_ISSUED_AT, $issued_at);
    }


    public function getExpiresIn()
    {
        return $this->_getData(self::KEY_EXPIRES_IN);
    }


    /**
     * Set expires_in
     *
     * @param string $expires_in
     * @return $this
     */
    public function setExpiresIn($expires_in)
    {
        return $this->setData(self::KEY_EXPIRES_IN, $expires_in);
    }


}
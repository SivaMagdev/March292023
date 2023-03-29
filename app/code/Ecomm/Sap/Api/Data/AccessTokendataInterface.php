<?php

namespace Ecomm\Sap\Api\Data;

/**
 * @api
 */
interface AccessTokendataInterface
{

    /**
     * Get access_token
     *
     * @return string
     */
    public function getAccessToken();

      /**
     * Set access_token
     *
     * @param string $access_token
     * @return $this
     */
    public function setAccessToken($access_token);

    /**
     * Get token_type
     *
     * @return string
     */
    public function getTokenType();

      /**
     * Set token_type
     *
     * @param string $token_type
     * @return $this
     */
    public function setTokenType($token_type);

    /**
     * Get issued_at
     *
     * @return string
     */
    public function getIssuedAt();

      /**
     * Set issued_at
     *
     * @param string $issued_at
     * @return $this
     */
    public function setIssuedAt($issued_at);

    /**
     * Get expires_in
     *
     * @return string
     */
    public function getExpiresIn();

      /**
     * Set expires_in
     *
     * @param string $expires_in
     * @return $this
     */
    public function setExpiresIn($expires_in);

}
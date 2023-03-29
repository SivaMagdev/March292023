<?php
namespace Ecomm\Api\Api;

/**
 * Interface ContactusManagementInterface
 *
 * @package Ecomm\Api\Api
 */
interface ContactusManagementInterface
{
    /**
     * Contact us form.
     *
     * @param mixed $contactForm
     *
     * @return \Ecomm\Api\Api\Data\ContactusInterface
     */
    public function submitForm($contactForm);
}
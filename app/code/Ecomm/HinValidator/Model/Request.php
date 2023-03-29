<?php
/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_HinValidator
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\HinValidator\Model;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Request
{

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * The Order Send to SAP
     *
     * @param Curl $curl
     * @param Json $json
     **/
    public function __construct(
        Curl $curl,
        Json $json,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Store order Details and send the order Details to SAP
     *
     * @param Array $payload
     * @return json
     */
    protected function request($payload)
    {
        /** Fetch config values from store configurations  */
        $username = $this->getUsername();
        $password = $this->getPassword();
        $url = $this->getHinUrl();

        try {
            $this->curl->setOption(CURLOPT_USERPWD, $username . ":" . $password);
            $this->curl->setOption(CURLOPT_HEADER, 0);
            $this->curl->setOption(CURLOPT_TIMEOUT, 60);
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'GET');
            $this->curl->addHeader("Content-Type", "application/json");
        
            $this->curl->post($url, $this->json->serialize($payload));
            $response =  $this->curl->getBody();

        } catch (\Exception $e) {
            $response = null;
        }
        return $response;
    }

    /**
     * Store order Details and send the order Details to SAP
     *
     * @param array $payload
     * @return array
     */
    public function push($payload)
    {
        $response = $this->request($payload);
        return $response;
    }

    /**
      * Get url from store config settings
      */
    protected function getHinUrl()
    {
        return $this->scopeConfig->getValue(
            "hin_settings/general/hin_url",
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
      * Get username from store config settings
      */
    protected function getUsername()
    {
        return $this->scopeConfig->getValue(
            "hin_settings/general/hin_username",
            ScopeInterface::SCOPE_STORE
        );
    }

     /**
      * Get password from store config settings
      */
    protected function getPassword()
    {
        return $this->scopeConfig->getValue(
            "hin_settings/general/hin_password",
            ScopeInterface::SCOPE_STORE
        );
    }
}

<?php
namespace Ecomm\Api\Model;

use Ecomm\Api\Api\AccessTokenInterface;
use Ecomm\Api\Helper\Data;

class AccessToken implements AccessTokenInterface
{

	protected $_request;

	protected $_urlInterface;

	protected $_adminTokenServiceInterface;

    protected $_dataFactory;

    protected $_helper;

    protected $_logger;

	public function __construct(
       \Magento\Framework\App\RequestInterface $request,
       \Magento\Framework\UrlInterface $urlInterface,
       \Magento\Integration\Api\AdminTokenServiceInterface $adminTokenServiceInterface,
       \Ecomm\Api\Api\Data\AccessTokendataInterfaceFactory $dataFactory,
       Data $helper,
       \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_request 					= $request;
        $this->_urlInterface 				= $urlInterface;
        $this->_adminTokenServiceInterface 	= $adminTokenServiceInterface;
        $this->_dataFactory     			= $dataFactory;
        $this->_helper          			= $helper;
        $this->_logger          			= $logger;
    }

	public function getAccessToken() {

		$page_object 	= $this->_dataFactory->create();



		$json = array();

		try {
			$requestData = json_decode($this->_request->getContent());
			//echo '<pre>'.print_r($requestData, true).'</pre>';
			//echo $this->_helper->getToken1();
			//echo $this->_helper->getToken2();
			//echo $this->_helper->getUsername();
			//echo $this->_helper->getPassword();
			if($requestData->secure_token != '' && ($requestData->secure_token == $this->_helper->getToken1() || $requestData->secure_token == $this->_helper->getToken2())) {

				$response = $this->_adminTokenServiceInterface->createAdminAccessToken($this->_helper->getUsername(), $this->_helper->getPassword());

				$page_object->setAccessToken($response);
				$page_object->setTokenType('Bearer');
				$page_object->setIssuedAt(date('Y-m-d\TH:i:s\Z'));
				$page_object->setExpiresIn('900');

			}
		} catch (\Exception $e) {
            $this->_logger->critical('Error message', ['exception' => $e]);
        }

        return $page_object;
	}
}
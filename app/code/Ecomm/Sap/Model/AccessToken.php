<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\AccessTokenInterface;

class AccessToken implements AccessTokenInterface
{

	protected $_request;

	protected $_urlInterface;

	protected $_adminTokenServiceInterface;

    protected $_dataFactory;

    protected $_logger;

	public function __construct(
       \Magento\Framework\App\RequestInterface $request,
       \Magento\Framework\UrlInterface $urlInterface,
       \Magento\Integration\Api\AdminTokenServiceInterface $adminTokenServiceInterface,
       \Ecomm\Sap\Api\Data\AccessTokendataInterfaceFactory $dataFactory,
       \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_request 					= $request;
        $this->_urlInterface 				= $urlInterface;
        $this->_adminTokenServiceInterface 	= $adminTokenServiceInterface;
        $this->_dataFactory     			= $dataFactory;
        $this->_logger          			= $logger;
    }

	public function getAccessToken() {

		$page_object 	= $this->_dataFactory->create();



		$json = array();

		try {
			if($this->_request->getParam('username') != '' && $this->_request->getParam('password') != '') {

				$response = $this->_adminTokenServiceInterface->createAdminAccessToken($this->_request->getParam('username'), $this->_request->getParam('password'));

				/*$json[] = [
					'access_token'=> $response,
					'token_type' => 'Bearer',
					'issued_at' => date('Y-m-d\TH:i:s'),
					'expires_in'=> '3600'];*/

				$page_object->setAccessToken($response);
				$page_object->setTokenType('Bearer');
				$page_object->setIssuedAt(date('Y-m-d\TH:i:s\Z'));
				$page_object->setExpiresIn('900');

			} else {
				//$json['error'] = 'Invalid credentials.';
			}
		} catch (\Exception $e) {
            $this->_logger->critical('Error message', ['exception' => $e]);
        }

        return $page_object;
	}
}
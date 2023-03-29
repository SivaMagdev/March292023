<?php
namespace Ecomm\Api\Model;

use Ecomm\Api\Api\VersionInfoInterface;
use Ecomm\Api\Helper\Data;

class VersionInfo implements VersionInfoInterface
{

	protected $_request;

	protected $_urlInterface;

    protected $_dataFactory;

    protected $_helper;

    protected $_logger;

	public function __construct(
       \Magento\Framework\App\RequestInterface $request,
       \Magento\Framework\UrlInterface $urlInterface,
       \Ecomm\Api\Api\Data\VersionInfodataInterfaceFactory $dataFactory,
       Data $helper,
       \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_request 					= $request;
        $this->_urlInterface 				= $urlInterface;
        $this->_dataFactory     			= $dataFactory;
        $this->_helper          			= $helper;
        $this->_logger          			= $logger;
    }

	public function getVersionInfo() {

		$page_object 	= $this->_dataFactory->create();



		$json = array();

		try {
			$page_object->setAppVersion($this->_helper->getAppVersion());
			$page_object->setTcVersion($this->_helper->getTcVersion());
			$page_object->setEulaVersion($this->_helper->getEulaVersion());
		} catch (\Exception $e) {
            $this->_logger->critical('Error message', ['exception' => $e]);
        }

        return $page_object;
	}
}
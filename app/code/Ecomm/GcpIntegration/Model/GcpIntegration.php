<?php
namespace Ecomm\GcpIntegration\Model;

use Ecomm\GcpIntegration\Api\GcpApiIntegrationInterface;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

class GcpIntegration implements GcpApiIntegrationInterface {

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

	/**
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        LoggerInterface $logger
    )
    {
        $this->request = $request;
    }

	/**
	 * {@inheritdoc}
	 */
	public function getPayload()
	{
		$returnData = [];

        $writer = new \Laminas\Log\Writer\Stream(BP . '/var/log/gcp_log.log');
        $logger = new  \Laminas\Log\Logger();
        $logger->addWriter($writer);

        $logger->info('Payload: '.$this->request->getContent());

        $returnData[] = [
            'success'=>true,
            'reference_id' => '',
            'msg' => ''
        ];

        return $returnData;
	}
}

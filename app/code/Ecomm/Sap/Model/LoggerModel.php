<?php
namespace Ecomm\Sap\Model;

class LoggerModel
{
    /**
     * Logging instance
     * @var \Ecomm\Sap\Logger\Logger
     */
    protected $_logger;

    /**
     * Constructor
     * @param \Ecomm\Sap\Logger\Logger $logger
     */
    public function __construct(
        \Ecomm\Sap\Logger\Logger $logger
    ) {
        $this->_logger = $logger;
    }

    public function createLog($requestcontent)
    {
        $this->_logger->info($requestcontent);
    }
}
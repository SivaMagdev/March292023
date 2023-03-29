<?php

namespace Ecomm\EmailToSalesRep\Plugin\Sales\Order\Email\Container;

use Psr\Log\LoggerInterface;
use Magento\LoginAsCustomerApi\Api\GetLoggedAsCustomerAdminIdInterface;
use Magento\User\Model\UserFactory;

class OrderIdentityPlugin
{
    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var GetLoggedAsCustomerAdminIdInterface
     */
    private $getLoggedAsCustomerAdminId;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        LoggerInterface $logger,
        UserFactory $userFactory,
        GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->logger               = $logger;
        $this->userFactory = $userFactory;
        $this->getLoggedAsCustomerAdminId = $getLoggedAsCustomerAdminId;
    }

    /**
     * @param \Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject
     * @param callable $proceed
     * @return bool
     */
    public function aroundgetEmailCopyTo(\Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject, callable $proceed)
    {
        $returnValue = $proceed();

        //$this->logger->info('EmailCopyTo:'.json_encode($returnValue, true));

        $adminId = $this->getLoggedAsCustomerAdminId->execute();
        if ($adminId) {
            $adminUser = $this->userFactory->create()->load($adminId);
            if($returnValue) {
	            $returnValue[] = $adminUser->getEmail();
	            //$this->logger->info('EmailCopyTo:'.json_encode($returnValue, true));
	            return $returnValue;
        	} else {
        		$data[] = $adminUser->getEmail();
	            //$this->logger->info('EmailCopyTo:'.json_encode($data, true));
	            return $data;
        	}
        }

        return $returnValue;
    }
}
<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecomm\Theme\Plugin;

use Magento\LoginAsCustomerApi\Api\GetLoggedAsCustomerAdminIdInterface;
use Magento\Sales\Model\Order;
use Magento\User\Model\UserFactory;
use Magento\Customer\Model\Session;

/**
 * Add comment after order placed by admin using Login as Customer.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class FrontAddCommentOnOrderPlacementPlugin
{
    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var GetLoggedAsCustomerAdminIdInterface
     */
    private $getLoggedAsCustomerAdminId;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param UserFactory $userFactory
     * @param GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId
     */
    public function __construct(
        UserFactory $userFactory,
        GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId,
        Session $customerSession
    ) {
        $this->userFactory = $userFactory;
        $this->getLoggedAsCustomerAdminId = $getLoggedAsCustomerAdminId;
        $this->customerSession = $customerSession;
    }

    /**
     * Add comment after order placed by admin using Login as Customer.
     *
     * @param Order $subject
     * @param Order $result
     * @return Order
     */
    public function afterPlace(Order $subject, Order $result): Order
    {
        $adminId = $this->getLoggedAsCustomerAdminId->execute();
        if ($adminId) {
            $adminUser = $this->userFactory->create()->load($adminId);
            $subject->setShadowLogin(1);
            $subject->setShadowLoginName($adminUser->getFirstName().' '.$adminUser->getLastName());
            $subject->addCommentToStatusHistory(
                "Order Placed by '{$adminUser->getFirstName()} {$adminUser->getLastName()}' on behalf of {$this->customerSession->getCustomer()->getName()}",
                false,
                true
            )->setIsCustomerNotified(true);
        }

        return $result;
    }
}

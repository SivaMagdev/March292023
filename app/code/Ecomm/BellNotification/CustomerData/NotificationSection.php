<?php
namespace Ecomm\BellNotification\CustomerData;
use Magento\Customer\CustomerData\SectionSourceInterface;

class NotificationSection implements SectionSourceInterface
{
    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext,
    	\Ecomm\BellNotification\Helper\BellNotification $bellNotificationHelper
    ) {
        $this->httpContext = $httpContext;
        $this->bellNotificationHelper = $bellNotificationHelper;
	}

	/**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
    	$counter = $this->bellNotificationHelper->getNotificationCount();

    	$param = ['counter'=> $counter];
        return $param;
    }
}
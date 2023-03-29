<?php
namespace Rage\DeliveryDate\Plugin\Checkout\Model;

class ShippingInformationManagement
{
    const XPATH_STATUS   = 'rg_deliverydate/general/enable';

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Psr\Log\LoggerInterface $logger,
        \Rage\DeliveryDate\Helper\Data $helperData
    ) {
        $this->quote_repository = $quoteRepository;
        $this->date = $dateTime;
        $this->logger = $logger;
        $this->helper_data = $helperData;
    }
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $module_status = $this->helper_data->getConfigValue(self::XPATH_STATUS);
        if (!$module_status) {
            return;
        }
        $extAttributes = $addressInformation->getExtensionAttributes();
        $delivery_date = $extAttributes->getRgddDeliveryDate();
        $delivery_comment = $extAttributes->getRgddDeliveryComment();
        $this->logger->addInfo("deliver date:" . $delivery_date);
        $this->logger->addInfo("deliver note:" . $delivery_comment);
        try {

            $quote = $this->quote_repository->getActive($cartId);
            if (!empty($delivery_date)) {
                if ($this->date->gmtDate('Y-m-d', $delivery_date)) {
                    $quote->setRgddDeliveryDate($delivery_date);
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Date'));
                }
            }
            $quote->setRgddDeliveryComment($delivery_comment);
        } catch (\Exception $e) {
            $this->logger->addInfo($e);
        }
    }
}

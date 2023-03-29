<?php
namespace Ecomm\CustomDiscount\Observer;

use Magento\Quote\Model\Quote;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Psr\Log\LoggerInterface;

/**
 * Class TotalsBeforeEvent
 */
class TotalsAfterEvent implements ObserverInterface
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger               = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        //$shippingAssignment = $observer->getEvent()->getShippingAssignment();
        $address = $quote->getAddress();

        // fetch quote data
        /** @var Quote $quote */

        // fetch totals data
        $total = $observer->getEvent()->getTotal();
        $address = $observer->getEvent()->getAddress();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $helper = $objectManager->create('Ecomm\CustomDiscount\Helper\Data');

        $quote=$observer->getEvent()->getQuote();
        $quoteid=$quote->getId();
        //check condition here if need to apply Discount
        // if($disocuntApply) $discountAmount =0;
        $discountAmount =0;

        if($quoteid) {

            $total=$quote->getBaseSubtotal();
            $canAddItems = $quote->isVirtual()? ('billing') : ('shipping');
            foreach ($quote->getAllAddresses() as $address) {

                if($address->getAddressType() == 'billing') {
                    $address =  $quote->getShippingAddress();
                }

                $rules= $helper->getBestRuleDetails($quote,$total,$address);
                if(!empty($rules)) {
                    /*$quote->setAppliedRuleIds('');
                    $quote->setAppliedRuleIds($rules['rule_id']);
                    $quote->save();

                    $address->setAppliedRuleIds('');
                    $address->setAppliedRuleIds($rules['rule_id']);
                    $address->save();*/
                    //$this->logger->info('Observer:'.print_r($rules, true));
                    $discountAmount = $rules['rule_amount'];
                    if($rules['free_shipping']) {
                        $address->setGrandTotal($total - $discountAmount + $address->getShippingAmount());

                        $address->setShippingDiscountAmount($discountAmount);
                        $address->setShippingDiscountAmount($discountAmount);
                    } else {
                       // $address->setBaseSubTotal($quote->getBaseSubTotal() - $discountAmount);
                        $address->setGrandTotal($total - $discountAmount + $address->getShippingAmount());
                        $address->setShippingDiscountAmount(0);
                        $address->setShippingDiscountAmount(0);
                    }

                    $address->setDiscountAmount(-($discountAmount));
                    $address->setBaseDiscountAmount(-($discountAmount));

                   // $address->setAppliedRuleIds($rules['rule_id']);
                  //  $quote->setAppliedRuleIds($rules['rule_id']);

                  $rule = $objectManager->create('Magento\SalesRule\Model\Rule')->load($rules['rule_id']);

                  $address->setDiscountDescription($rule->getName());


                } else {
                    $discountAmount = 0;
                    $address->setGrandTotal($total - $discountAmount + $address->getShippingAmount());
                    $address->setShippingDiscountAmount(0);
                    $address->setShippingDiscountAmount(0);
                    $address->setDiscountAmount(-($discountAmount));
                    $address->setBaseDiscountAmount(-($discountAmount));
                }

              }//end: if
            } //end: foreach
            //echo $quote->getGrandTotal();
       }
}
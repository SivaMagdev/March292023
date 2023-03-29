<?php
namespace Ecomm\SalesRepEmail\Plugin\Sales\Order\Email\Container;
  
 class OrderIdentityPlugin
 {
     /**
      * @var \Magento\Checkout\Model\Session $checkoutSession
      */
     protected $checkoutSession;
  
     /**
      * @param \Magento\Checkout\Model\Session $checkoutSession
      *
      * @codeCoverageIgnore
      */
     public function __construct(
         \Magento\Checkout\Model\Session $checkoutSession
     )
     {
         $this->checkoutSession = $checkoutSession;
     }
  
     /**
      * @param \Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject
      * @param callable $proceed
      * @return bool
      */
     public function aroundIsEnabled(\Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject, callable $proceed)
     {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('plugin');
         $returnValue = $proceed();
  
         $forceOrderMailSentOnSuccess = $this->checkoutSession->getForceOrderMailSentOnSuccess();
         if(isset($forceOrderMailSentOnSuccess) && $forceOrderMailSentOnSuccess)
         {
             if($returnValue)
                 $returnValue = false;
             else
                 $returnValue = true;
  
             $this->checkoutSession->unsForceOrderMailSentOnSuccess();
         }
  
         return $returnValue;
     }
 }
<?php
namespace Ecomm\Restriction\Controller\Index;

use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;

class Save extends \Magento\Framework\App\Action\Action
{
	/**
     * @var Blogcategeory
     */
	private $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;
     protected  $_modelCart;
     protected $checkoutSession;


    public function __construct(
		Context $context,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
         \Magento\Customer\Model\Session $customerSession,
        LoggerInterface $logger,
        CheckoutSession $checkoutSession,
        Cart $modelCart
    ) {
    	 $this->customerSession = $customerSession;
    	$this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
         $this->checkoutSession = $checkoutSession;
         $this->_modelCart = $modelCart;
        parent::__construct($context);
    }
	public function execute()
    {
         $data = $this->getRequest()->getParam('id');

       $customerId=$this->customerSession->getCustomer()->getId();
        try {
            $address = $this->addressRepository->getById($data)->setCustomerId($customerId);
            $address->setIsDefaultShipping(true);

            $this->addressRepository->save($address);
            if($this->addressRepository->save($address)){
            $this->messageManager->addSuccessMessage(__('You Default Address is Changed.'));
            // $cart = $this->_modelCart;
            // $quoteItems = $this->checkoutSession->getQuote()->getItemsCollection();
            // foreach($quoteItems as $item)
            // {
            // $cart->removeItem($item->getId())->save(); 
            // }
 
        }
        else{
            $this->messageManager->addErrorMessage(__('Data was not saved.'));
        }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath("checkout/cart/");
        return $resultRedirect;


      
    }
   
}

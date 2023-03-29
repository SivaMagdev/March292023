<?php
namespace Ecomm\PriceEngine\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Ecomm\PriceEngine\Block\CustomPriceLogic;
use Magento\Customer\Model\Session;
use Ecomm\PriceEngine\Model\RestrictionProduct;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Model\ProductRepository;

class PriceUpdate implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    protected $logger;
    public $request;
    private $customPriceLogic;
    private $customerSession;
    private $restrictionProduct;
    private $message;
    private $productRepository;
 
    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request,
        CustomPriceLogic $customPriceLogic,
        Session $customerSession,
        RestrictionProduct $restrictionProduct,
        ManagerInterface $message,
        ProductRepository $productRepository
    )
    {
        $this->request = $request;
        $this->logger = $logger;
        $this->_messageManager = $messageManager;
        $this->customPriceLogic = $customPriceLogic;
        $this->customerSession = $customerSession;
        $this->restrictionProduct = $restrictionProduct;
        $this->message = $message;
        $this->productRepository = $productRepository;
    }
 
    /**
     * add to cart event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {   

        $item = $observer->getQuoteItem();
        $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
        $priceType = '';
        $post = $this->request->getPost();
        $_product = $item->getProduct();
        $restrictionProduct = $this->productRepository->get($item->getSku());
        $restriction = $restrictionProduct->getResource()->getAttribute('limit_restrictions')->setStoreId(0)->getFrontend()->getValue($restrictionProduct);
        
        if($restriction == 'Yes' && $item->getOptionByCode('option_ids') == null){
            $qty = $observer->getQuoteItem()->getQty();

         $output =  $this->restrictionProduct->productRestrictions($restrictionProduct, $qty, $this->getCustomerId()); 
         if(!empty($output)){
            if($output['qty'] > 0){
                $item->setQty($output['qty']);
                $item->save();
                $this->message->addErrorMessage($output['message']);
            }else{

                if($this->request->getParam('page')  == 'pdp'){
                   $item->delete();
                }
                $this->message->addErrorMessage($output['message']);
            }
         }
          
        }
        if($post){
            $data = $this->request->getParam('price_type');
            $data = explode('/', $data);
            $priceType = $data[0];
        }

        if($post && $this->request->getParam('price_type') == null  && $item->getOptionByCode('option_ids') == null){
            $price = $this->customPriceLogic->getCustomRegularPrice($this->customerSession->getCustomer()->getId(), $observer->getEvent()->getData('product'));
            $item->setCustomPrice($price['price']);
            $item->setOriginalCustomPrice($price['price']);
            $item->setData('price_type', $price['price_type']);
            $item->getProduct()->setIsSuperMode(true);
        }
        if ($data != null) {
            if ($priceType == "regular_price" && $item->getOptionByCode('option_ids') == null) {
                $price = $this->customPriceLogic->getCustomRegularPrice($this->customerSession->getCustomer()->getId(), $observer->getEvent()->getData('product'));
                $item->setCustomPrice($price['price']);
                $item->setOriginalCustomPrice($price['price']);
                $item->setData('price_type', $price['price_type']);
                $item->getProduct()->setIsSuperMode(true);
            } else if ($priceType == "sub_wac_price" && $item->getOptionByCode('option_ids') == null) {
                $price = $this->customPriceLogic->get340bPrice('sub_wac', $observer->getEvent()->getData('product'));
                $item->setCustomPrice($price['price']);
                $item->setOriginalCustomPrice($price['price']);
                $item->setData('price_type', $price['price_type']);
                $item->getProduct()->setIsSuperMode(true);
            } else if ($priceType == "phs_price" && $item->getOptionByCode('option_ids') == null) {
                $price = $this->customPriceLogic->get340bPrice('phs_indirect', $observer->getEvent()->getData('product'));
                $item->setCustomPrice($price['price']);
                $item->setOriginalCustomPrice($price['price']);
                $item->setData('price_type', $price['price_type']);
                $item->getProduct()->setIsSuperMode(true);
            } 
 
        return $this;
        }
    }

    public function getCustomerId()
    {   
        $customer = $this->customerSession;
        if($customer->isLoggedIn()) {
            return $customerId = $customer->getId();
        }
    }
}
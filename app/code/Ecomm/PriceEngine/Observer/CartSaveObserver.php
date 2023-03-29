<?php
namespace Ecomm\PriceEngine\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Ecomm\PriceEngine\Block\CustomPriceLogic;
use Magento\Customer\Model\Session;
use Ecomm\PriceEngine\Model\RestrictionProduct;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;

class CartSaveObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    protected $logger;
    public  $request;
    private $customPriceLogic;
    private $customerSession;
    private $restrictionProduct;
    private $productRepository;
    private $quoteRepo;
    private $defualtShippingAddress;

 
    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        LoggerInterface $logger,
        ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request,
        CustomPriceLogic $customPriceLogic,
        Session $customerSession,
        RestrictionProduct $restrictionProduct,
        ProductRepository $productRepository,
        CartRepositoryInterface $quoteRepo,
        AccountManagementInterface $defualtShippingAddress
    )
    {
        $this->request = $request;
        $this->logger = $logger;
        $this->_messageManager = $messageManager;
        $this->customPriceLogic = $customPriceLogic;
        $this->customerSession = $customerSession;
        $this->restrictionProduct = $restrictionProduct;
        $this->productRepository = $productRepository;
        $this->quoteRepo = $quoteRepo;
        $this->defualtShippingAddress = $defualtShippingAddress;
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
         $items = $observer->getCart()->getQuote();
         $info = $observer->getInfo()->getData();
        
         foreach($items->getItems() as $item){
            if($item->getOptionByCode('option_ids') == null){
           $_product = $this->productRepository->get($item->getSku());
           $price = $this->priceUpdater($item, $_product);
           $item->setCustomPrice($price['price']);
           $item->setOriginalCustomPrice($price['price']);
           $item->setData('price_type',$price['price_type']);
           $item->save();
           $restriction = $_product->getResource()->getAttribute('limit_restrictions')->setStoreId(0)->getFrontend()->getValue($_product);
            if($restriction == 'Yes' ){
                $quote = $this->quoteRepo->getActive($items->getId());
                $output =  $this->restrictionProduct->productRestrictions($_product, $item->getQty(), $this->getCustomerId());
                if(!empty($output)){
                    $quoteItem = $quote->getItemById($item->getId());
                    if($output['qty'] > 0){
                        $quoteItem->setQty($output['qty']);
                        $quoteItem->save();
                        $this->_messageManager->addErrorMessage($output['message']);
                        die;
                    }
                    else{
                        $item->delete(); 
                        continue;
                        $this->message->addErrorMessage($output['message']);                  
                    }
                }
            }
        }
         }
         return $this;        
    }

    public function getCustomerId()
    {   
        $customer = $this->customerSession;
        if($customer->isLoggedIn()) {
            return $customerId = $customer->getId();
        }
    }

    private function priceUpdater($item,$product){
        $price = [];
        $addressInfo = $this->defualtShippingAddress->getDefaultShippingAddress($this->getCustomerId());
        $hinStatus = '';
        if ($addressInfo->getCustomAttribute('hin_status') != null) {
        $hinStatus = $addressInfo->getCustomAttribute('hin_status')->getValue();
        }

        if($hinStatus == 1){
            if($item->getData('price_type') == 'Price'){
                $price = $this->customPriceLogic->getCustomRegularPrice($this->getCustomerId(), $product);
            }else if($item->getData('price_type') == '340b(Phs Indirect Price)'){
                $price = $this->customPriceLogic->get340bPrice('phs_indirect', $product);    
            }else if($item->getData('price_type') == '340b(Sub-WAC Price)'){
                $price = $this->customPriceLogic->get340bPrice('sub_wac', $product);    
            }
        }else {
            $price = $this->customPriceLogic->getCustomRegularPrice($this->getCustomerId(), $product);
        }

        return $price;
    }
}
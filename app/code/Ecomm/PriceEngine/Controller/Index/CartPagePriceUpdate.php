<?php
namespace Ecomm\PriceEngine\Controller\Index;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ecomm\PriceEngine\Block\CustomPriceLogic;
use Magento\Catalog\Model\ProductRepository;

class CartPagePriceUpdate extends \Magento\Framework\App\Action\Action
{
/**
* @var CartRepositoryInterface
*/
private $quoteRepo;
private $jsonResultFactory;
private $customPriceLogic;
private $product;

public function __construct(
Context $context,
CartRepositoryInterface $quoteRepo,
JsonFactory $jsonResultFactory,
CustomPriceLogic $customPriceLogic,
ProductRepository $product
)
{
$this->quoteRepo = $quoteRepo;
$this->jsonResultFactory = $jsonResultFactory;
$this->customPriceLogic = $customPriceLogic;
$this->product = $product;
parent::__construct($context);
}

public function execute()
{
$data = [];
$result = $this->jsonResultFactory->create();

$price = $this->getRequest()->getParam('price');
$run = explode('/',$price);

if(count($run) == 5){
$cart = $this->quoteRepo->getActive($run[2]);
if($this->validation($run,$cart)){
foreach($cart->getItems() as $items){
if($run[4] == $items->getSku() && $run[3] == $items->getId()){
$productData = $this->product->getById($items->getProductId());
if($run[1] == 'regular_price'){
$finalPrice = $this->customPriceLogic->getCustomRegularPrice($cart->getCustomerId(),$productData);
if($finalPrice != null){
$quoteItem = $cart->getItemById($items->getId());
$quoteItem->setCustomPrice($finalPrice['price']);
$quoteItem->setOriginalCustomPrice($finalPrice['price']);
$quoteItem->setData('price_type', $finalPrice['price_type']);
$quoteItem->getProduct()->setIsSuperMode(true);
$quoteItem->save();
$data['status'] = 'success';
}else{
$data['status'] = 'error';
}
}else{
$finalPrice = $this->customPriceLogic->get340bPrice($run[1],$productData);
if($finalPrice != null){
$quoteItem = $cart->getItemById($items->getId());
$quoteItem->setCustomPrice($finalPrice['price']);
$quoteItem->setOriginalCustomPrice($finalPrice['price']);
$quoteItem->setData('price_type', $finalPrice['price_type']);
$quoteItem->getProduct()->setIsSuperMode(true);
$quoteItem->save();
$data['status'] = 'success';
}else{
$data['status'] = 'error';
}
}
}
}
}else{
$data['status'] = 'error';
}
}else{
$data['status'] = 'error';
}

$result->setData($data);
return $result;
}

private function validation($data, $cart){
if($data[0] != $cart->getCustomerId()){
return false;
}

return true;
}
}

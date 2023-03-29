<?php

namespace Ecomm\Api\Plugin;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
/**
 * Class OrderRepositoryPlugin
 */
class OrderRepositoryPlugin
{
    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;


    public function __construct(
        OrderExtensionFactory $extensionFactory,
        ProductRepositoryInterfaceFactory $productRepository
    )
    {
        $this->extensionFactory = $extensionFactory;
        $this->productRepository = $productRepository;
    }

     /**
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes) {
            $extensionAttributes->setHasInvoices($order->hasInvoices());
            $extensionAttributes->setSapId($order->getSapId());
            $extensionAttributes->setRgddDeliveryDate($order->getRgddDeliveryDate());
            $extensionAttributes->setRgddDeliveryComment($order->getRgddDeliveryComment());
            $order->setExtensionAttributes($extensionAttributes);
        }

    if ($order->getItems()) {
            foreach ($order->getItems() as $item) {
                $extensionAttributes = $item->getExtensionAttributes();
                $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
                $productData = $this->productRepository->create();
                try {
                    $product = $productData->get($item->getSku());
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                    $product = false;
                }
                if($product && $product->getThumbnail()){
                    $extensionAttributes->setProductimage($product->getThumbnail());
                } else {
                    $extensionAttributes->setProductimage('');
                }

                if($item->getProductOptions()){
                    //echo '<pre>'.print_r($item->getProductOptions(), true).'</pre>';

                    $item_options = $item->getProductOptions();

                    $shortdated_batch_id = '';

                    if(isset($item_options['options'])) {

                        //echo '<pre>'.print_r($item_options['options'], true).'</pre>';

                        foreach($item_options['options'] as $options){

                            $shortdated_batch_id = $options['label'].' '.$options['value'];

                        }
                    }
                    $extensionAttributes->setBatchNumber($shortdated_batch_id);
                } else {
                    $extensionAttributes->setBatchNumber('');
                }

                $priceType = $item->getPriceType();
                
                if ($priceType) {
                    $extensionAttributes->setPriceType($priceType);
                } else {
                    $extensionAttributes->setPriceType('');
                }

                $item->setExtensionAttributes($extensionAttributes);

            }
        }

        return $order;
    }

    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Model\ResourceModel\Order\Collection $resultOrder
    ) {
        foreach ($resultOrder->getItems() as $order) {
            $this->afterGet($subject, $order);
        }

        return $resultOrder;
    }
}
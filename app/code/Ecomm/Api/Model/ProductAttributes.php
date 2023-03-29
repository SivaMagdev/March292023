<?php
namespace Ecomm\Api\Model;

use Ecomm\Api\Api\ProductAttributesInterface;

class ProductAttributes implements ProductAttributesInterface
{
	protected $_dataFactory;

    protected $_itemsdataFactory;

    protected $_optionsdataFactory;

    protected $_productCollectionFactory;

    protected $productCollection;

    protected $productStatus;

    protected $attributFactory;

    protected $eavConfig;

    protected $_request;

    private $_logger;

	public function __construct(
        \Ecomm\Api\Api\Data\ProductAttributesItemsdataInterfaceFactory $dataFactory,
        \Ecomm\Api\Api\Data\AttributeItemsdataInterfaceFactory $itemsdataFactory,
        \Ecomm\Api\Api\Data\AttributeOptionsdataInterfaceFactory $optionsdataFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\ResourceModel\Product $productFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attributFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\RequestInterface $request,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_dataFactory                 = $dataFactory;
        $this->_itemsdataFactory            = $itemsdataFactory;
        $this->_optionsdataFactory          = $optionsdataFactory;
        $this->eavConfig                    = $eavConfig;
        $this->attributFactory              = $attributFactory;
        $this->_productCollectionFactory    = $productFactory;
        $this->productCollection            = $productCollection;
        $this->productStatus                = $productStatus;
        $this->_request                     = $request;
        $this->_logger                      = $logger;
    }

	public function getAttributes() {

        $page_object    = $this->_dataFactory->create();

        try {
            $requestData = json_decode($this->_request->getContent());

            $attribute = $this->eavConfig->getAttribute('catalog_product', $requestData->attribute_code);

            if($requestData->attribute_code != ''){

               /* $attributeCode = $requestData->attribute_code;
                $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
                $attributFactory = $this->attributFactory->create()->setStoreFilter(0, false);
                 $itemCollection = $this->_productCollectionFactory;
                $attributFactory->getSelect()
                ->joinLeft(
                    array('value_table' => $itemCollection->getTable('catalog_product_entity_int')),
                    'main_table.option_id=value_table.value AND main_table.attribute_id=value_table.attribute_id', 'entity_id')
                ->reset(\Zend_Db_Select::COLUMNS)
                ->columns(array('main_table.option_id',new \Zend_Db_Expr('COUNT(value_table.entity_id)')))
                ->where('main_table.attribute_id=:attribute_id')
                ->group('main_table.option_id');
                $result = $itemCollection->getConnection()->fetchPairs(
                $attributFactory->getSelect(), array('attribute_id' => $attribute->getId()));
                echo '<pre>test'.print_r($result, true).'</pre>'; exit();*/

            }
        } catch (\Exception $e) {
            $this->_logger->critical('Error message', ['exception' => $e]);
        }


        $items_array = [];

        //foreach($order->getData() as $data){

            $order_object = $this->_itemsdataFactory->create();


            $options_array = [];

            $options = $attribute->getSource()->getAllOptions();
            foreach ($options as $option) {

                $orderitem_object = $this->_optionsdataFactory->create();

                $products = $this->productCollection->create()
                    ->addAttributeToSelect($requestData->attribute_code)
                    ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
                    ->addAttributeToFilter($requestData->attribute_code, array('eq' => $option['value']));

                $product_counts = count($products);

                $orderitem_object->setLabel($option['label']);
                $orderitem_object->setValue($option['value']);
                $orderitem_object->setProductCount($product_counts);

                $options_array[] =  $orderitem_object;
            }

            $order_object->setOptions($options_array);

            $items_array[] =  $order_object;
        //}

        if($items_array) {
            $page_object->setItems($items_array);
        }

        return $page_object;

	}
}
<?php
namespace Ecomm\Supportdocument\Ui\Component\Create\Form;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;


class Product implements OptionSourceInterface
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $productTree;

    /**
     * @param CustomerCollectionFactory $productCollectionFactory
     * @param RequestInterface $request
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        RequestInterface $request
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getCustomerTree();
    }

    /**
     * Retrieve categories tree
     *
     * @return array
     */
    protected function getCustomerTree()
    {
        if ($this->productTree === null) {
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            // $collection->addNameToSelect();

            foreach ($collection as $product) {
                $productId = $product->getEntityId();
                if (!isset($productById[$productId])) {
                    $productById[$productId] = [
                        'value' => $productId
                    ];
                }
                $productById[$productId]['label'] = $product->getName();
            }
            $this->productTree = $productById;
        }
        return $this->productTree;
    }
}
<?php
namespace Ecomm\PriceEngine\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Catalog\Model\ProductRepository;
class GenericName extends Column
{
    protected $_productRepository;

    public function __construct(
        ProductRepository $productRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $data = [],
        array $components = []
    ) {

        $this->_productRepository = $productRepository;
        parent::__construct($context, $uiComponentFactory,$components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                ///$item[$this->getData('name')] = $this->logger->get($item['entity_id'])->getLastLoginAt();
                // var_dump($item);
                // die;
             $product = $this->_productRepository->get($item['sku']);
            //  var_dump($product->getName());
            //  die;

                if ($product) {
                    $item[$this->getData('name')] = $product->getName();
                } else {
                    $item[$this->getData('name')] = '';
                }
            }
        }
        return $dataSource;
    }
}

<?php

namespace Ecomm\Api\Plugin;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ProductCustomOptionsValue
{
    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @param ProductCustomOptionRepositoryInterface $optionRepository
     */
    public function __construct(
        ProductCustomOptionRepositoryInterface $optionRepository,
        \Ecomm\Api\Helper\OptionsProvider $optionProvider,
        \Magento\Catalog\Block\Product\View\Options $productOptions
    ) {
        $this->optionRepository = $optionRepository;
        $this->optionProvider = $optionProvider;
        $this->productOptions = $productOptions;
    }

    public function afterExecute(\Magento\Catalog\Model\Product\Option\ReadHandler $subject, $result, $entity, $arguments = [])
    {
        $options = $dataOption = [];
        $productSku = $entity->getSku();
        $options = $this->productOptions->decorateArray($this->optionRepository->getProductOptions($entity));
        $dataOption = $this->optionProvider->getExtendedOptionsConfig($options, $productSku);

        // $urlHttp = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Request\Http');
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');

        if($dataOption != '' && $dataOption != null && $dataOption != '[]' && str_contains($urlInterface->getCurrentUrl(), 'rest/V1/ecomm-api/products/') != null){
            $entity->setOptions($dataOption);
        }else{
           $options = [];
            /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $log = $objectManager->get('\Psr\Log\LoggerInterface');
            $level = 'ERROR';
            foreach ($this->optionRepository->getProductOptions($entity) as $option) {
                // $log->debug($ob_get_level(),$option->groupFactory());
                $option->setProduct($entity);
                $options[] = $option;
            }
            $entity->setOptions($options);
        }
        return $entity;
    }
}
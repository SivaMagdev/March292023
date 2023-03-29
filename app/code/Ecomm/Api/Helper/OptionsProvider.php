<?php

declare(strict_types=1);

namespace Ecomm\Api\Helper;

use Magento\Framework\Pricing\Helper\Data;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * class OptionsProvider
 */
class OptionsProvider
{
    /**
     * @var array
     */
    protected $productOptions;

    /**
     * Constructor
     *
     * @param array $components
     */
    public function __construct(
        \Magento\Catalog\Block\Product\View\Options $productOptions,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        Data $priceHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        ImageHelper $imageHelper,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->productOptions = $productOptions;
        $this->_jsonEncoder = $jsonEncoder;
        $this->priceHelper = $priceHelper;
        $this->jsonHelper = $jsonHelper;
        $this->imageHelper = $imageHelper;
        $this->_filesystem = $filesystem;
    }

    public function getExtendedOptionsConfig($options, $productSku = '')
    {
        $config = $result = [];
        /** @var \Magento\Catalog\Model\Product\Option $option */
        if (empty($options)) {
            return $this->_jsonEncoder->encode($config);
        }

        foreach ($options as $option) {
            $config[$option->getId()]['product_sku']= $productSku;
            $config[$option->getId()]['option_id']= $option->getId();
            $config[$option->getId()]['title']= $option->getTitle();
            $config[$option->getId()]['type']= $option->getType();
            $config[$option->getId()]['sort_order']= $option->getSortOrder();
            $config[$option->getId()]['is_require']= $option->getIsRequire();
            $config[$option->getId()]['max_characters']= $option->getMaxCharacters();
            $config[$option->getId()]['image_size_x']= $option->getImageSizeX();
            $config[$option->getId()]['image_size_y']= $option->getImageSizeY();

            /** @var \Magento\Catalog\Model\Product\Option\Value $value */
            if (empty($option->getValues())) {
                continue;
            }
            $i = 0;
            foreach ($option->getValues() as $value) {
                $config[$option->getId()]['values'][$i]['title'] = $value->getTitle();
                $config[$option->getId()]['values'][$i]['sort_order'] = $value->getSortOrder();
                $config[$option->getId()]['values'][$i]['price'] = $value->getDefaultPrice();
                $config[$option->getId()]['values'][$i]['price_type'] = $value->getPriceType();
                $config[$option->getId()]['values'][$i]['option_type_id'] =  $value->getId();
                $config[$option->getId()]['values'][$i]['quantity'] = $value->getQuantity();
                $config[$option->getId()]['values'][$i]['expiry_date'] = $value->getExpiryDate();
                $config[$option->getId()]['values'][$i]['option_id'] = $value->getOptionId();
                $config[$option->getId()]['values'][$i]['sku'] = $value->getSku();
                $config[$option->getId()]['values'][$i]['default_title'] = $value->getDefaultTitle();
                $config[$option->getId()]['values'][$i]['store_title'] = $value->getStoreTitle();
                $config[$option->getId()]['values'][$i]['default_price'] = $value->getDefaultPrice();
                $config[$option->getId()]['values'][$i]['default_price_type'] = $value->getDefaultPriceType();
                $config[$option->getId()]['values'][$i]['store_price'] = $value->getStorePrice();
                $config[$option->getId()]['values'][$i]['store_price_type'] = $value->getStorePriceType();
                $i++;
            }
        }
        return $config;
    }

    public function getDependency($dependency){

        $dependencyArray=[];
        $dependency = json_decode($dependency);
        foreach($dependency as $key=>$value)
        {
            $dependencyArray['key'][$key]=$value[0];
            $dependencyArray['value'][$key]=$value[1];

         }
        return $dependencyArray;
    }
}
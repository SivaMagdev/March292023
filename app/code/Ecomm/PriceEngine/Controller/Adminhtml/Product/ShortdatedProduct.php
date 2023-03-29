<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Option;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Ecomm\PriceEngine\Model\StockFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Eav\Model\Config;
use Ecomm\PriceEngine\Model\ShortdatedpriceFactory;

/**
 * Export shortdated product controller class
 */
class ShortdatedProduct extends \Magento\Backend\App\Action
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Option
     */
    protected $customOption;

    /**
     * @var GetSalableQuantityDataBySku
     */
    protected $getSalableQuantityDataBySku;

    /**
     * @var StockFactory
     */
    protected $stockFactory;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var ShortdatedpriceFactory
     */
    protected $shortdatedpriceFactory;

    /**
     * Constructor
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param CollectionFactory $productCollectionFactory
     * @param ProductFactory $productFactory
     * @param Option $customOption
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     * @param StockFactory $stockFactory
     * @param TimezoneInterface $timezone
     * @param DateTime $date
     * @param Config $eavConfig
     * @param ShortdatedpriceFactory $shortdatedpriceFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        CollectionFactory $productCollectionFactory,
        ProductFactory $productFactory,
        Option $customOption,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        StockFactory $stockFactory,
        TimezoneInterface $timezone,
        DateTime $date,
        Config $eavConfig,
        ShortdatedpriceFactory $shortdatedpriceFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory  = $productFactory;
        $this->customOption  = $customOption;
        $this->getSalableQuantityDataBySku  = $getSalableQuantityDataBySku;
        $this->stockFactory  = $stockFactory;
        $this->timezone = $timezone;
        $this->date = $date;
        $this->eavConfig = $eavConfig;
        $this->shortdatedpriceFactory = $shortdatedpriceFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $name = 'shortdated-product-'.date('m-d-Y-H-i-s');
        $filepath = 'export/' .$name. '.csv';
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $columns = ['SKU', 'NDC','Generic Name','Batch', 'Expiration date', 'Price', 'Price Exp Date','Quantity','Quantity Valid date'];

        foreach ($columns as $column) {
            $header[] = $column;
        }

        $stream->writeCsv($header);

        $categoryType = $this->getAttributeValues('categorytype');

        $shortdatedId = array_search('Special buy', $categoryType);

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*')->addAttributeToFilter(
            array(
                array('attribute'=>'categorytype','eq'=>$shortdatedId)
            )
        );

        $current_date = $this->timezone->date($this->date->date('Y-m-d H:i:s'))->format('Y-m-d H:i:s');

        foreach ($productCollection as $productItem) {

            $product = $this->productFactory->create()->load($productItem->getId());

            foreach ($product->getOptions() as $option) {
                foreach ($option->getValues() as $value) {
                    $shortdatedPrice = $this->shortdatedpriceFactory->create()->getCollection()->addFieldToFilter('end_date', ['lt' => $current_date])->addFieldToFilter('batch', ['eq' => $value->getTitle()])->setOrder('shortdated_price_id', 'DESC')->getFirstItem();
                    $validityEndDate = '';
                    if ($shortdatedPrice->getData()) {
                        $validityEndDate = $shortdatedPrice->getData()['end_date'];
                    }
                    $itemData = [];
                    $itemData[] = $product->getMaterial();
                    $itemData[] = $product->getSku();
                    $itemData[] = $product->getName();
                    $itemData[] = $value->getTitle();
                    $itemData[] = $value->getExpiryDate();
                    $itemData[] = $value->getPrice();
                    $itemData[] = $validityEndDate;
                    $itemData[] = $value->getQuantity();
                    $itemData[] = $validityEndDate;
                    $stream->writeCsv($itemData);

                }
            }
        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = $name.'.csv';
        return $this->fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);

    }

    private function getAttributeValues($attribute_code){
        $available_values_list = [];
        $all_options = $this->eavConfig->getAttribute('catalog_product', $attribute_code)->getSource()->getAllOptions();
        $available_values_list = [];
        foreach ($all_options as $option) {
            if ($option['value'] > 0) {
                $available_values_list[$option['value']] = $option['label'];
            }
        }
        return $available_values_list;
    }
}

<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Ecomm\PriceEngine\Model\StockFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Export regular product controller class
 */
class RegularProduct extends \Magento\Backend\App\Action
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
     * Constructor
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param CollectionFactory $productCollectionFactory
     * @param ProductFactory $productFactory
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     * @param StockFactory $stockFactory
     * @param TimezoneInterface $timezone
     * @param DateTime $date
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        CollectionFactory $productCollectionFactory,
        ProductFactory $productFactory,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        StockFactory $stockFactory,
        TimezoneInterface $timezone,
        DateTime $date
    ) {
        $this->fileFactory = $fileFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory  = $productFactory;
        $this->getSalableQuantityDataBySku  = $getSalableQuantityDataBySku;
        $this->stockFactory  = $stockFactory;
        $this->timezone = $timezone;
        $this->date = $date;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $name = 'regular-product-'.date('m-d-Y-H-i-s');
        $filepath = 'export/' .$name. '.csv';
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $columns = ['SKU', 'NDC','Generic Name','Price','Price Expiration','Quantity','Quantity Valid Upto'];

        foreach ($columns as $column) {
            $header[] = $column;
        }

        $stream->writeCsv($header);

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');

        $current_date = $this->timezone->date($this->date->date('Y-m-d H:i:s'))->format('Y-m-d H:i:s');

        foreach ($productCollection as $productItem) {

            $itemData = [];

            $product = $this->productFactory->create()->load($productItem->getId());

            $salable = $this->getSalableQuantityDataBySku->execute($product->getSku());

            $stocks = $this->stockFactory->create()->getCollection()->addFieldToFilter('start_date', ['lt' => $current_date])->addFieldToFilter('product_sku', ['eq' => $product->getMaterial()])->getFirstItem();

            $salableQty = 0;
            if (isset($salable[0]['qty'])) {
                $salableQty = $salable[0]['qty'];
            }

            $stockEndDate = '';
            if ($stocks->getData()) {
                $stockEndDate = $stocks->getData()['end_date'];
            }

            $itemData[] = $product->getMaterial();
            $itemData[] = $product->getSku();
            $itemData[] = $product->getName();
            $itemData[] = $product->getPrice();
            $itemData[] = $product->getPriceEffectiveTo();
            $itemData[] = $salableQty;
            $itemData[] = $stockEndDate;
            $stream->writeCsv($itemData);
        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = $name.'.csv';
        return $this->fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);

    }
}

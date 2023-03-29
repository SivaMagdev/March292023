<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Shortdatedprice;

use Magento\Framework\App\Filesystem\DirectoryList;
use Ecomm\PriceEngine\Model\ShortdatedpriceFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

/**
 * Reularpriceupdate controller class
 */
class Sdpriceupdate extends \Magento\Backend\App\Action
{
    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $helper;

    protected $_date;

    protected $_productFactory;

    protected $_productRepository;

    protected $_modelProduct;

    protected $_optionFactory;

    protected $_productCollectionFactory;

    protected $_customerGroup;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $directoryList;

    protected $_eavConfig;

    protected $_shortdatedpriceFactory;

    protected $indexFactory;

    protected $indexCollection;

    /**
     * Constructor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        DirectoryList $directoryList,
        ShortdatedpriceFactory $shortdatedpriceFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\Product $modelProduct,
        \Magento\Catalog\Model\Product\Option $optionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\File\Csv $csv,
        \Magento\Indexer\Model\IndexerFactory $indexFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexCollection,
        TypeListInterface $typeListInterface,
        Pool $pool
    ) {
        $this->_shortdatedpriceFactory = $shortdatedpriceFactory;
        $this->_transportBuilder    = $transportBuilder;
        $this->inlineTranslation    = $inlineTranslation;
        $this->scopeConfig          = $scopeConfig;
        $this->helper               = $helper;
        $this->resultPageFactory    = $resultPageFactory;
        $this->directoryList        = $directoryList;
        $this->_date                = $date;
        $this->_productFactory      = $productFactory;
        $this->_productRepository   = $productRepository;
        $this->_modelProduct        = $modelProduct;
        $this->_optionFactory       = $optionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_customerGroup       = $customerGroup;
        $this->_eavConfig           = $eavConfig;
        $this->csv = $csv;
        $this->indexFactory              = $indexFactory;
        $this->indexCollection           = $indexCollection;
        $this->typeListInterface         = $typeListInterface;
        $this->pool                      = $pool;
        parent::__construct($context);
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecomm_PriceEngine::shortdatedprice_import');
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        //echo 'test'; exit();

        $returnData = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of Object manageraa

        $today_date = $this->_date->date('Y-m-d');
        //echo $today_date.'<br />';
        $_shortdated_price_collections = $this->_shortdatedpriceFactory->create()->getCollection()->addFieldToFilter('start_date', $today_date);

        $_shortdated_price_collections->getSelect()->group('product_sku');

        $import = 0;

        $not_exists = '';

        //$regular_prices = $_shortdated_price_collections->getSelect()->group('product_sku');

        //echo '<pre>Test'.print_r($_shortdated_price_collections->getData(),true).'</pre>'; exit();
        //echo sizeof($_shortdated_price_collections); exit();
        if($_shortdated_price_collections) {

            $categorytype = $this->_eavConfig->getAttribute('catalog_product', 'categorytype');
            $category_types = [];
            foreach ($categorytype->getSource()->getAllOptions() as $option) {
                if ($option['value'] > 0) {
                    $category_types[$option['value']] = $option['label'];
                }
            }

            //echo '<pre>category_types: '.print_r($category_types, true).'</pre>'; exit();
            $shortdated_id = array_search("Special buy",$category_types);
            // echo 'shortdated_id: '.$shortdated_id.'<br />'; exit();
            foreach($_shortdated_price_collections->getData() as $collection_product){

                //echo '<pre>'.print_r($collection_product,true).'</pre>'; exit();

                //echo $collection_product['ndc'].'<br />';

                try {
                    //$_product = $this->_productRepository->get($collection_product['ndc']);
                    //echo $collection_product['ndc'].'<br />';
                    //echo $collection_product['product_sku'].'<br />';
                    $_product = $this->_productFactory->create()->loadByAttribute('material', $collection_product['product_sku']);
                    if($_product) {

                        //echo $_product->getId().'<br />';

                        $_collections_by_product = $this->_shortdatedpriceFactory->create()->getCollection()->addFieldToFilter('start_date', $today_date)->addFieldToFilter('ndc', $collection_product['ndc']);

                        //echo '<pre>'.print_r($_collections_by_product->getData(), true).'</pre>';

                        $product = $this->_modelProduct->load($_product->getId());

                        $_product->setCategorytype($shortdated_id);

                        $availabe_categories_type = '';

                        $availabe_categories_types = [];

                        if($product->getCustomAttribute('categorytype')){
                            $availabe_categories_type = $product->getCustomAttribute('categorytype')->getValue();
                        }

                        $availabe_categories_types = explode(',',$availabe_categories_type);
                        //echo '<pre>availabe_categories_type: '.print_r($availabe_categories_types, true).'</pre>'; exit();

                        if(!in_array($shortdated_id, $availabe_categories_types)){
                            $availabe_categories_types[] = $shortdated_id;
                        }

                        $sorder_order = 0;

                        $existing_values = [];
                        $existing_batches = [];

                        $option_id = '';

                        $customOptions = $this->_optionFactory->getProductOptionCollection($product);

                        foreach ($customOptions as $customOption) {

                            $option_id = $customOption->getOptionId();

                            if($customOption->getValues()){
                                foreach($customOption->getValues() as $value) {

                                    $existing_batches[$value->getOptionTypeId()] = trim($value->getTitle());

                                    $existing_values[$value->getOptionTypeId()] =[
                                        'option_type_id' => $value->getOptionTypeId(),
                                        'title' => trim($value->getTitle()),
                                        'price' => $value->getPrice(),
                                        'price_type' => 'fixed',
                                        'sku' => '',
                                        'sort_order' => $value->getSortOrder(),
                                        'store_id' => 0,
                                        'quantity' => $value->getQuantity(),
                                        'expiry_date' => $value->getExpiryDate(),
                                    ];

                                }
                            }

                        }
                        //echo '<pre>: '.print_r($existing_values, true).'</pre>';

                        $optionsArray = [];
                        $values = [];

                        foreach($_collections_by_product->getData() as $shortdated_price){

                            if(in_array(trim($shortdated_price['batch']), $existing_batches)){

                                $array_option_index =  array_search(trim($shortdated_price['batch']),$existing_batches);

                                //echo '<pre>: '.print_r($existing_values[$array_option_index], true).'</pre>';
                                $values[$array_option_index] =[
                                    'option_type_id' => $array_option_index,
                                    'title' => trim($shortdated_price['batch']),
                                    'price' => $shortdated_price['shortdated_price'],
                                    'price_type' => 'fixed',
                                    'sku' => '',
                                    'sort_order' => $sorder_order,
                                    'store_id' => 0,
                                    'quantity' => $shortdated_price['inventory'],
                                    'expiry_date' => $shortdated_price['expiry_date'],
                                ];

                            } else {

                                $values[] =[
                                    'title' => trim($shortdated_price['batch']),
                                    'price' => $shortdated_price['shortdated_price'],
                                    'price_type' => 'fixed',
                                    'sku' => '',
                                    'sort_order' => $sorder_order,
                                    'store_id' => 0,
                                    'quantity' => $shortdated_price['inventory'],
                                    'expiry_date' => $shortdated_price['expiry_date'],
                                ];
                            }

                            $sorder_order++;
                        }

                        if($option_id == '') {
                            $optionsArray = [
                                [
                                    'title' => __('Short Dated LOT No#'),
                                    'type' => 'radio',
                                    'is_require' => 0,
                                    'sort_order' => 1,
                                    'store_id' => 0,
                                    'values' => $values,
                                ]
                            ];

                        } else {
                            $optionsArray = [
                                [
                                    'option_id' => $customOption->getOptionId(),
                                    'title' => $customOption->getTitle(),
                                    'type' => 'radio',
                                    'is_require' => 0,
                                    'sort_order' => 1,
                                    'store_id' => 0,
                                    'values' => $values,
                                ]
                            ];

                        }

                        //echo '<pre>'.print_r($optionsArray, true).'</pre>';

                        try {

                            $product->setHasOptions(1);
                            $product->setCanSaveCustomOptions(true);

                            //echo '<pre>'.print_r($optionsArray, true).'</pre>';
                            //echo $product->getRowId();

                            foreach ($optionsArray as $optionValue) {
                                $option = $this->_optionFactory->setProductId($product->getRowId())->setStoreId(0)->addData($optionValue);
                                $option->save();
                                $_product->addOption($option);
                            }
                            $this->_productRepository->save($_product);
                            // $product->save();

                            $import++;

                        } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                            $not_exists.='<br />' . __('SKU') . ' <b>"' . $shortdated_price['product_sku'] . '"</b> ';
                        }

                    } else {
                        $not_exists.='<br />' . __('SKU') . ' <b>"' . $shortdated_price['product_sku'] . ' Not exist in product master"</b> ';
                    }

                } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                    $not_exists.='<br />' . __('SKU') . ' <b>"' . $collection_product['product_sku'] . '"</b> ';
                }
                //echo '<pre>'.print_r($returnData, true).'</pre>';
                //exit();
            }
        }

        //echo '<pre>'.print_r($returnData, true).'</pre>';

        //exit();

        if ($import > 0) {
            $this->messageManager->addSuccess(__('Shortdated Price Updated successfully.') . $not_exists);
        } else {
            $this->messageManager->addError(__('No Shortdated Price Updated.') . $not_exists);
        }
        //echo '<pre>'.print_r($returnData, true).'</pre>'; exit();

        //echo $not_exists; exit();

        $this->doReindexing();

        $this->cachePrograme();

        $this->_redirect('*/*/import');
    }

    private function doReindexing() {
        $indexerCollection = $this->indexCollection->create();
        $indexids = $indexerCollection->getAllIds();

        foreach ($indexids as $indexid){
            $indexidarray = $this->indexFactory->create()->load($indexid);
            $indexidarray->reindexRow($indexid);
        }
    }

    private function cachePrograme() {

        $_cacheTypeList = $this->typeListInterface;

        $_cacheFrontendPool = $this->pool;

        $types = array('full_page');

        foreach ($types as $type) {
            $_cacheTypeList->cleanType($type);
        }

        foreach ($_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}

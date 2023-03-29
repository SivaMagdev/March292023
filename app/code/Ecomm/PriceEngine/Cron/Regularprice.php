<?php

namespace Ecomm\PriceEngine\Cron;

use \Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Ecomm\PriceEngine\Model\RegularPriceFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

class Regularprice
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

    protected $_productCollectionFactory;

    protected $_customerGroup;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $directoryList;

    protected $_regularpriceFactory;

    protected $indexFactory;

    protected $indexCollection;

    protected $logger;

	public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        DirectoryList $directoryList,
        RegularPriceFactory $regularpriceFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Magento\Framework\File\Csv $csv,
        \Magento\Indexer\Model\IndexerFactory $indexFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexCollection,
        TypeListInterface $typeListInterface,
        Pool $pool,
        LoggerInterface $logger)
	{
		$this->_regularpriceFactory = $regularpriceFactory;
        $this->_transportBuilder    = $transportBuilder;
        $this->inlineTranslation    = $inlineTranslation;
        $this->scopeConfig          = $scopeConfig;
        $this->helper               = $helper;
        $this->resultPageFactory    = $resultPageFactory;
        $this->directoryList        = $directoryList;
        $this->_date                = $date;
        $this->_productFactory      = $productFactory;
        $this->_productRepository   = $productRepository;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_customerGroup            = $customerGroup;
        $this->csv                       = $csv;
        $this->indexFactory              = $indexFactory;
        $this->indexCollection           = $indexCollection;
        $this->typeListInterface         = $typeListInterface;
        $this->pool                      = $pool;
        $this->logger 				= $logger;
	}


  	public function execute()
	{

        //echo 'test'; exit();

        $regularprices = [];

        $customer_groups = $this->getCustomerGroups();

        //echo '<pre>'.print_r($customer_groups, true).'</pre>'; exit();

        $today_date = $this->_date->date('Y-m-d');
        //echo $today_date.'<br />';
        $_regular_price_collections = $this->_regularpriceFactory->create()->getCollection()->addFieldToFilter('start_date', $today_date);

        $_regular_price_collections->getSelect()->group('ndc');

        $import = 0;

        $not_exists = '';

        //$regular_prices = $_regular_price_collection->getSelect()->group('product_sku');

        //echo '<pre>Test'.print_r($_regular_price_collection->getData(),true).'</pre>';
        //exit();
        //echo sizeof($_regular_price_collections); exit();
        if(sizeof($_regular_price_collections) > 0) {
            /*$collection = $this->_productCollectionFactory->create();
            //$collection->addAttributeToSelect('*');
            foreach($collection->getData() as $product){

                //echo $product['entity_id'].'<br />';
                $_product = $this->_productFactory->create()->load($product['entity_id']);

                //echo $_product->getId().'<br />';
                $tierPriceProduct = [];
                $_product->setData ('tier_price', $tierPriceProduct);
                $_product->save(); // its remove the customizable options
                //$this->_productRepository->save($_product);

            }*/

            foreach($_regular_price_collections->getData() as $collection_product){

                try {
                    //$_product = $this->_productRepository->get($collection_product['ndc']);
                    $_product = $this->_productFactory->create()->loadByAttribute('material', $collection_product['product_sku']);

                    if($_product){

                        $_collections_by_product = $this->_regularpriceFactory->create()->getCollection()->addFieldToFilter('start_date', $today_date)->addFieldToFilter('ndc', $collection_product['ndc']);

                        $product = $this->_productFactory->create()->load($_product->getId());

                        $tierPriceProduct = [];
                        $product->setData ('tier_price', $tierPriceProduct);
                        $product->save(); // its remove the customizable options

                        $tierPrices = [];

                        //echo '<pre>'.print_r($_collections_by_product->getData(), true).'</pre>';

                        foreach($_collections_by_product->getData() as $regular_price){
                            //echo '<pre>'.print_r($regular_price, true).'</pre>';
                            //$_product = $this->_productRepository->get($regular_price['product_sku']);

                            $product->setPriceEffectiveFrom($regular_price['start_date']);
                            $product->setPriceEffectiveTo($regular_price['end_date']);

                            if($regular_price['gpo_name'] != '') {

                                $group_id = array_search($regular_price['gpo_name'],$customer_groups);

                                //echo 'group_id: '.$group_id.':: '.$regular_price['gpo_name'].': '.$regular_price['gpo_price'].'<br />';

                                $tierPrices[] = array(
                                    'website_id'  => 0,
                                    'cust_group'  => $group_id,
                                    'price_qty'   => 1.00,
                                    'price'       => (float)$regular_price['gpo_price']
                                );

                            } else {
                                //echo 'general'.$regular_price['direct_price'].'<br />';
                                $product->setPrice($regular_price['direct_price']);
                            }
                        }
                        //echo '<pre>'.print_r($tierPrices, true).'</pre>';

                        $product->setTierPrice($tierPrices);

                        $import++;

                        try {
                            $product->save();
                            $this->_productRepository->save($product);

                           /* $returnData[] = array(
                                "article_code"=>$regular_price['product_sku'],
                                "status"=>1,
                                "error_code"=>"Article Price Updated.",
                            );*/
                        } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                            /*$returnData[] = array(
                                "article_code"=>$regular_price['product_sku'],
                                "status"=>0,
                                "error_code"=>$e->getMessage(),
                            );*/
                            $not_exists.='<br />' . __('SKU') . ' <b>"' . $regular_price['product_sku'] . '"</b> ';
                        }
                    }

                } catch (\Exception $e) {
                    //$this->messageManager->addError($e->getMessage());
                    /*$returnData[] = array(
                        "article_code"=>$regular_price['product_sku'],
                        "status"=>0,
                        "error_code"=>"Article not found.",
                    );*/
                    $not_exists.='<br />' . __('SKU') . ' <b>"' . $collection_product['product_sku'] . '"</b> ';
                }
            }
        }

        //echo '<pre>'.print_r($returnData, true).'</pre>';

        if ($import > 0) {
            $this->logger->log('ERROR','Regular Price Updated successfully:',[$not_exists]);
        } else {
            $this->logger->log('ERROR','No Regular Price Updated:',[$not_exists]);
        }

        $this->doReindexing();
        $this->cachePrograme();

	}

    /**
     * Get customer groups
     *
     * @return array
     */
    private function getCustomerGroups() {
        $groups = [];
        $customerGroups = $this->_customerGroup->toOptionArray();
        foreach($customerGroups as $customerGroup){
            $groups[$customerGroup['value']] = $customerGroup['label'];
        }
        return $groups;
    }

    private function doReindexing() {
        $indexerCollection = $this->indexCollection->create();
        $indexids = $indexerCollection->getAllIds();

        foreach ($indexids as $indexid){
            $indexidarray = $this->indexFactory->create()->load($indexid);

            //If you want reindex all use this code.
            //$indexidarray->reindexAll($indexid);

            //If you want to reindex one by one, use this code
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
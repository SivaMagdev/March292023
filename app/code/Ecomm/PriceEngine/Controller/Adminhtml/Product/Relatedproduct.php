<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Product;

use Magento\Framework\App\Filesystem\DirectoryList;
//use Ecomm\PriceEngine\Model\ProductFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

/**
 * Relatedproduct controller class
 */
class Relatedproduct extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $_productObject;

    protected $_productFactory;

    protected $_productRepository;

    protected $_productCollectionFactory;

    protected $_productLinks;

    protected $_eavConfig;

    protected $indexFactory;

    protected $indexCollection;

    /**
     * Constructor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\Product $productObject,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\Data\ProductLinkInterface $productLinks,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Indexer\Model\IndexerFactory $indexFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexCollection,
        TypeListInterface $typeListInterface,
        Pool $pool
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_productObject  = $productObject;
        $this->_productFactory  = $productFactory;
        $this->_productRepository = $productRepository;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productLinks = $productLinks;
        $this->_eavConfig = $eavConfig;
        $this->indexFactory              = $indexFactory;
        $this->indexCollection           = $indexCollection;
        $this->typeListInterface         = $typeListInterface;
        $this->pool                      = $pool;
        parent::__construct($context);
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecomm_PriceEngine::product_import');
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $isPost = $this->getRequest()->isPost();
        try {
            if ($isPost) {

                $productCollection = $this->_productCollectionFactory->create();
                $productCollection->addAttributeToSelect('*');

                $obj = \Magento\Framework\App\ObjectManager::getInstance();

                foreach ($productCollection as $_product) {
                    //echo $_product->getTheraputicCat().'<br />';
                    //echo $_product->getSku().'<br />';
                    $relatedproducts = $productCollection->addAttributeToFilter(array(array('attribute'=>'theraputic_cat','eq'=>$_product->getTheraputicCat())));
                    $relatedProductsArranged = [];
                    $linkDataAll = [];

                    if($relatedproducts->getData()){
                        $product = $this->_productFactory->create()->load($_product->getId());
                        $inc = 0;
                        foreach($relatedproducts->getData() as $related_product){
                            //echo '<pre>'.print_r($related_product, true).'</pre>';
                            if($related_product['entity_id'] != $_product->getId()){
                                //echo $related_product['sku'].'<br />';
                                //$skuLinks[] = $related_product['sku'];
                                //$relatedProductsArranged[$related_product['entity_id']] = array('position' => $inc);
                                $productLinks = $obj->create('Magento\Catalog\Api\Data\ProductLinkInterface');
                                $linkData = $productLinks //Magento\Catalog\Api\Data\ProductLinkInterface
                                    ->setSku($_product->getSku())
                                    ->setLinkedProductSku($related_product['sku'])
                                    ->setLinkType("related");
                                $linkDataAll[] = $linkData;
                                $inc++;
                            }
                        }
                        if($linkDataAll) {
                            //echo count($linkDataAll).'<br />';
                            $product->setProductLinks($linkDataAll);
                        }
                        //$product->setRelatedLinkData($relatedProductsArranged);
                        $product->save();
                        //echo '<pre>'.print_r($relatedProductsArranged, true).'</pre>';

                        //
                    }

                    //echo 'Count: '.sizeof($relatedproducts->getData()).'<br />';
                    //echo '<pre>'.print_r($relatedproducts->getData(), true).'</pre>';
                    //echo "<br>";
                }

                //echo 'test';
                //exit();

                //echo $import;
                $this->messageManager->addSuccess(__('Related Product Mapped successfully.'));

            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->doReindexing();

        $this->cachePrograme();

        $this->_redirect('*/*/import');
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

<?php
namespace Ecomm\PriceEngine\Controller\Shortdatedprice;

use Ecomm\PriceEngine\Model\ShortdatedpriceFactory;

class Import extends \Magento\Framework\App\Action\Action
{
	protected $_shortdatedpriceFactory;

	protected $_date;

    protected $_productFactory;

    protected $_productRepository;

    protected $_optionFactory;

    protected $_customerGroup;

	protected $_resultJsonFactory;

    protected $_productCollectionFactory;

	public function __construct(
		ShortdatedpriceFactory $shortdatedpriceFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\Product\Option $optionFactory,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{

        $this->_shortdatedpriceFactory = $shortdatedpriceFactory;
        $this->_date 				= $date;
        $this->_productFactory  	= $productFactory;
        $this->_productRepository 	= $productRepository;
        $this->_optionFactory   = $optionFactory;
        $this->_customerGroup     	= $customerGroup;
        $this->_resultJsonFactory 	= $resultJsonFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
		$this->_pageFactory 		= $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{

		$returnData = [];

		$customer_groups = $this->getCustomerGroups();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of Object manager
		//echo '<pre>'.print_r($customer_groups, true).'</pre>';

		$today_date = $this->_date->date('Y-m-d');
		//echo $today_date.'<br />';
		$_shortdated_price_collections = $this->_shortdatedpriceFactory->create()->getCollection()->addFieldToFilter('start_date', $today_date);

        $_shortdated_price_collections->getSelect()->group('product_sku');

        //$regular_prices = $_regular_price_collection->getSelect()->group('product_sku');

		//echo '<pre>Test'.print_r($_regular_price_collection->getData(),true).'</pre>';
        //exit();
        //echo sizeof($_regular_price_collection);
        if($_shortdated_price_collections) {
            foreach($_shortdated_price_collections->getData() as $collection_product){

                //echo '<pre>'.print_r($collection_product,true).'</pre>'; exit();

                //echo $collection_product['ndc'].'<br />';

                try {
                    $_product = $this->_productRepository->get($collection_product['ndc']);

                    $_collections_by_product = $this->_shortdatedpriceFactory->create()->getCollection()->addFieldToFilter('start_date', $today_date)->addFieldToFilter('ndc', $collection_product['ndc']);

                    $product = $this->_productFactory->create()->load($_product->getId());

                    $values = [];
                    $optionsArray = [];

                    $sorder_order = 0;

                    foreach($_collections_by_product->getData() as $shortdated_price){

                        $values[] =[
                            'title' => $shortdated_price['batch'],
                            'price' => $shortdated_price['shortdated_price'],
                            'price_type' => 'fixed',
                            'sku' => '',
                            'sort_order' => $sorder_order,
                            'quantity' => $shortdated_price['inventory'],
                            'expiry_date' => $shortdated_price['expiry_date'],
                        ];
                        $sorder_order++;
                    }

                    $optionsArray = [
                        [
                            'title' => 'Short Dated',
                            'type' => 'radio',
                            'is_require' => 0,
                            'sort_order' => 1,
                            'values' => $values,
                        ]
                    ];

                    //echo '<pre>'.print_r($optionsArray, true).'</pre>';

                    //$_productRepository = $objectManager->create('\Magento\Catalog\Model\Product\Option);

                    try {

                        $product->setHasOptions(1);
                        $product->setCanSaveCustomOptions(true);

                        foreach ($optionsArray as $optionValue) {
                            $option = $objectManager->create('\Magento\Catalog\Model\Product\Option')
                                        ->setProductId($_product->getId())
                                        ->setStoreId($_product->getStoreId())
                                        ->addData($optionValue);
                            $option->save();
                            $_product->addOption($option);
                            // must save product to add options in product
                            //$product->save($_product);
                            $this->_productRepository->save($_product);
                        }
                        //$product->save();

                        $returnData[] = array(
                            "article_code"=>$shortdated_price['ndc'],
                            "status"=>1,
                            "error_code"=>"Article Price Updated.",
                        );
                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                        $returnData[] = array(
                            "article_code"=>$shortdated_price['ndc'],
                            "status"=>0,
                            "error_code"=>$e->getMessage(),
                        );
                    }

        		} catch (\Magento\Framework\Exception\NoSuchEntityException $e){

                    $returnData[] = array(
                        "article_code"=>$collection_product['ndc'],
                        "status"=>0,
                        "error_code"=>"Article not found.",
                    );
                }
                //echo '<pre>'.print_r($returnData, true).'</pre>';
                //exit();
            }
        }

		echo '<pre>'.print_r($returnData, true).'</pre>';

		//return $returnData;

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

}
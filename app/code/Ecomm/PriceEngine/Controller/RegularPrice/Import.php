<?php
namespace Ecomm\PriceEngine\Controller\RegularPrice;

use Ecomm\PriceEngine\Model\RegularPriceFactory;

class Import extends \Magento\Framework\App\Action\Action
{
	protected $_regularpriceFactory;

	protected $_date;

    protected $_productFactory;

    protected $_productRepository;

    protected $_customerGroup;

	protected $_resultJsonFactory;

    protected $_productCollectionFactory;

	public function __construct(
		RegularPriceFactory $regularpriceFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{

        $this->_regularpriceFactory = $regularpriceFactory;
        $this->_date 				= $date;
        $this->_productFactory  	= $productFactory;
        $this->_productRepository 	= $productRepository;
        $this->_customerGroup     	= $customerGroup;
        $this->_resultJsonFactory 	= $resultJsonFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
		$this->_pageFactory 		= $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{

		$regularprices = [];

		$customer_groups = $this->getCustomerGroups();

		//echo '<pre>'.print_r($customer_groups, true).'</pre>';

		$today_date = $this->_date->date('Y-m-d');
		//echo $today_date.'<br />';
		$_regular_price_collections = $this->_regularpriceFactory->create()->getCollection()->addFieldToFilter('start_date', $today_date);

        $_regular_price_collections->getSelect()->group('ndc');

        //$regular_prices = $_regular_price_collection->getSelect()->group('product_sku');

		//echo '<pre>Test'.print_r($_regular_price_collection->getData(),true).'</pre>';
        //exit();
        //echo sizeof($_regular_price_collection);
        if($_regular_price_collections) {
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            foreach($collection->getData() as $product){

                //echo $product['entity_id'].'<br />';
                $_product = $this->_productFactory->create()->load($product['entity_id']);

                //echo $_product->getId().'<br />';
                $tierPriceProduct = [];
                $_product->setData ('tier_price', $tierPriceProduct);
                $_product->save();

            }

            foreach($_regular_price_collections->getData() as $collection_product){

                $_product = $this->_productRepository->get($collection_product['ndc']);

                if($_product->getId() > 0){


                    $_collections_by_product = $this->_regularpriceFactory->create()->getCollection()->addFieldToFilter('start_date', $today_date)->addFieldToFilter('ndc', $collection_product['ndc']);

                    $product = $this->_productFactory->create()->load($_product->getId());

                    $tierPrices = [];

                    foreach($_collections_by_product->getData() as $regular_price){
                        //echo '<pre>'.print_r($regular_price, true).'</pre>';
                        //$_product = $this->_productRepository->get($regular_price['product_sku']);

                        $product->setPriceEffectiveFrom($regular_price['start_date']);
                        $product->setPriceEffectiveTo($regular_price['end_date']);

                        if($regular_price['gpo_name'] != '') {

                            $group_id = array_search($regular_price['gpo_name'],$customer_groups);

                            //echo 'group_id: '.$group_id.': '.$regular_price['gpo_price'].'<br />';

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

                    try {
                        $product->save();

                        $returnData[] = array(
                            "article_code"=>$regular_price['product_sku'],
                            "status"=>1,
                            "error_code"=>"Article Price Updated.",
                        );
                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                        $returnData[] = array(
                            "article_code"=>$regular_price['product_sku'],
                            "status"=>0,
                            "error_code"=>$e->getMessage(),
                        );
                    }

        		} else {

                    $returnData[] = array(
                        "article_code"=>$regular_price['product_sku'],
                        "status"=>0,
                        "error_code"=>"Article not found.",
                    );
                }
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
<?php
namespace Ecomm\PriceEngine\Controller\Inventory;

use Ecomm\PriceEngine\Model\StockFactory;

class Import extends \Magento\Framework\App\Action\Action
{
	protected $_stockFactory;

	protected $_date;

    protected $_productFactory;

    protected $_productRepository;

    protected $_resultJsonFactory;

	public function __construct(
		StockFactory $stockFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{

        $this->_stockFactory = $stockFactory;
        $this->_date 				= $date;
        $this->_productFactory  	= $productFactory;
        $this->_productRepository 	= $productRepository;
        $this->_resultJsonFactory 	= $resultJsonFactory;
		$this->_pageFactory 		= $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{

		$regularprices = [];

		$today_date = $this->_date->date('Y-m-d');
		//echo $today_date.'<br />';
		$_stocks = $this->_stockFactory->create()->getCollection()->addFieldToFilter('start_date', $today_date);

		//echo '<pre>'.print_r($_stocks->getData(), true).'</pre>'; exit();
		foreach($_stocks->getData() as $regular_price){

			//echo '<pre>'.print_r($regular_price, true).'</pre>';
			$_product = $this->_productRepository->get($regular_price['product_sku']);

			if($_product->getId() > 0){

                $is_in_stock = 0;

                if($regular_price['stock'] > 0){
                    $is_in_stock = 1;
                }

                $_product->setStockData(array(
                    'use_config_manage_stock' => 0, //'Use config settings' checkbox
                    'manage_stock' => 1, //manage stock
                    'max_sale_qty' => 1000, //Maximum Qty Allowed in Shopping Cart
                    'notify_stock_qty' => 50, //Notify Qty below
                    'is_in_stock' => $is_in_stock, //Stock Availability
                    'qty' => $regular_price['stock'] //qty
                    )
                );



                try {
                            //$this->tierPrice->add($sku, $tierPriceData);

                    $_product->save();

                    $returnData[] = array(
                        "article_code"=>$regular_price['product_sku'],
                        "status"=>1,
                        "error_code"=>"Article stock Updated.",
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

		//echo '<pre>'.print_r($returnData, true).'</pre>';

		//return $returnData;

	}

}
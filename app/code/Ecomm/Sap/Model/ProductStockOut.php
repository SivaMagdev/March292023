<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\ProductStockOutInterface;

class ProductStockOut implements ProductStockOutInterface
{
	/**
     * Return the sum of the two numbers.
     *
     * @api
     * @param int $num1 Left hand operand.
     * @param int $num2 Right hand operand.
     * @return int The sum of the two values.
     */
    protected $_dataFactory;

    public $_request;

    protected $_productFactory;

    protected $_productRepository;

    /**
     * @var Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $_stockStateInterface;

    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    protected $_loggerFactory;

    protected $_logger;

	public function __construct(
        \Ecomm\Sap\Api\Data\ProductStockOutdataInterfaceFactory $dataFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\CatalogInventory\Api\StockStateInterface $stockStateInterface,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Ecomm\Sap\Model\LoggerModel $loggerFactory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_dataFactory = $dataFactory;
        $this->_request         = $request;
        $this->_productFactory  = $productFactory;
        $this->_productRepository = $productRepository;
        $this->_stockStateInterface = $stockStateInterface;
        $this->_stockRegistry = $stockRegistry;
        $this->_loggerFactory  = $loggerFactory;
        $this->_logger          = $logger;
    }

	public function getStockDetails() {

        $returnData = [];

        $this->_loggerFactory->createLog('StockDetailsReq: '.$this->_request->getContent());

        //$this->_logger->critical('ProductStock', ['data' => $this->_request->getContent()]);

        $requestData = json_decode($this->_request->getContent());

        if($requestData){
            //echo '<pre>'.print_r($requestData, true).'</pre>';
            foreach($requestData as $stockData){

                try {
                    $_product = $this->_productRepository->get($stockData->article_code);
                    //echo 'getEntityId: '.$_product->getEntityId().'<br />';

                    //echo $_product->getId().'<br />';
                    if($_product->getId() > 0){

                        $product = $this->_productFactory->create()->load($_product->getId());

                        $is_in_stock = 0;

                        if($stockData->quantity > 0){
                            $is_in_stock = 1;
                        }

                        $product->setStockData(array(
                            'use_config_manage_stock' => 0, //'Use config settings' checkbox
                            'manage_stock' => 1, //manage stock
                            'min_qty' => 0, //Out-of-Stock Threshold
                            'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
                            'max_sale_qty' => 1000, //Maximum Qty Allowed in Shopping Cart
                            'is_in_stock' => $is_in_stock, //Stock Availability
                            'qty' => $stockData->quantity //qty
                            )
                        );

                        $product->save();

                        $returnData[] = array(
                            "article_code"=>$stockData->article_code,
                            "status"=>1,
                            "error_code"=>"Article stock Updated.",
                        );
                    } else {
                        $returnData[] = array(
                            "article_code"=>$stockData->article_code,
                            "status"=>0,
                            "error_code"=>"Article Not Found.",
                        );
                    }

                } catch (\Magento\Framework\Exception\NoSuchEntityException $e){

                    //$error = json_decode($e);

                    //echo '<pre>'.print_r($error, true).'</pre>';
                    // insert your error handling here
                   $returnData[] = array(
                        "article_code"=>$stockData->article_code,
                        "status"=>0,
                        "error_code"=>$e->getMessage(),
                    );
                }

            }
        }

        $this->_loggerFactory->createLog('StockDetailsRes: '.json_encode($returnData));

        return $returnData;

	}
}
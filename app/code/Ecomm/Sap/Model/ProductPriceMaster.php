<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\ProductPriceMasterInterface;

class ProductPriceMaster implements ProductPriceMasterInterface
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

	public function getPriceDetails() {

        $returnData = [];

        $this->_loggerFactory->createLog('ArticlePriceReq: '.$this->_request->getContent());

        //$this->_logger->critical('ProductStock', ['data' => $this->_request->getContent()]);

        $requestData = json_decode($this->_request->getContent());

        if($requestData){
            //echo '<pre>'.print_r($requestData, true).'</pre>';
            foreach($requestData as $stockData){

                try {
                    $_product = $this->_productRepository->get($stockData->article_code);
                    //echo 'getEntityId: '.$_product->getEntityId().'<br />';

                    //echo $_product->getId().'<br />';
                    //echo $_product->getStoreId().'<br />';
                    //echo $stockData->name;
                    if($_product->getId() > 0){

                        $product = $this->_productFactory->create()->load($_product->getId());

                        $product->setPrice($stockData->price);

                        $product->save();

                        $returnData[] = array(
                            "article_code"=>$stockData->article_code,
                            "status"=>1,
                            "error_code"=>"Article Information Updated.",
                        );
                    } else {
                        $returnData[] = array(
                            "article_code"=>$stockData->article_code,
                            "status"=>0,
                            "error_code"=>"Article Not Found.",
                        );
                    }

                } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                    $returnData[] = array(
                        "article_code"=>$stockData->article_code,
                        "status"=>0,
                        "error_code"=>$e->getMessage(),
                    );
                }

            }
        }

        $this->_loggerFactory->createLog('ArticlePriceReq: '.json_encode($returnData));

        return $returnData;

	}
}
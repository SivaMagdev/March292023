<?php

namespace Ecomm\PriceEngine\Cron;

use \Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Ecomm\PriceEngine\Model\StockFactory;

class Inventory
{

	const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $helper;

    protected $_date;

    protected $_resourceConnection;

    protected $_productFactory;

    protected $_productRepository;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $directoryList;

    protected $_stockFactory;

    protected $logger;

	public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        DirectoryList $directoryList,
        StockFactory $stockFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\File\Csv $csv,
        LoggerInterface $logger)
	{
		$this->_transportBuilder    = $transportBuilder;
        $this->inlineTranslation    = $inlineTranslation;
        $this->scopeConfig          = $scopeConfig;
        $this->helper               = $helper;
        $this->_resourceConnection  = $resourceConnection;
        $this->resultPageFactory    = $resultPageFactory;
        $this->directoryList        = $directoryList;
        $this->_stockFactory        = $stockFactory;
        $this->_date                = $date;
        $this->_productFactory      = $productFactory;
        $this->_productRepository   = $productRepository;
        $this->csv                  = $csv;
        $this->logger 				= $logger;
	}


  	public function execute()
	{

		/*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info(__METHOD__);

		$this->logger->info('Price Engine Cron Works');*/

		//echo 'test'; exit();

        $today_date = $this->_date->date('Y-m-d');
        //echo $today_date.'<br />';
        $_stocks = $this->_stockFactory->create()->getCollection()->addFieldToFilter('start_date', $today_date);

        $import = 0;

        $not_exists = '';

        $connection = $this->_resourceConnection->getConnection();

        $adapter = $this->_resourceConnection->getConnection();
        $table = $this->_resourceConnection->getTableName('inventory_reservation');

        //echo '<pre>'.print_r($_stocks->getData(), true).'</pre>'; exit();
        foreach($_stocks->getData() as $regular_price){

            try {

                //echo '<pre>'.print_r($regular_price, true).'</pre>';
                //$_product = $this->_productRepository->get($regular_price['product_sku']);
                $_product = $this->_productFactory->create()->loadByAttribute('material', $regular_price['product_sku']);

                    $is_in_stock = 0;

                    if($regular_price['stock'] > 0){
                        $is_in_stock = 1;
                    }

                    $_product->setStockData(array(
                        'use_config_manage_stock' => 0, //'Use config settings' checkbox
                        'manage_stock' => 1, //manage stock
                        'max_sale_qty' => 10000, //Maximum Qty Allowed in Shopping Cart
                        'notify_stock_qty' => $regular_price['thereshold'], //Notify Qty below
                        'is_in_stock' => $is_in_stock, //Stock Availability
                        'qty' => $regular_price['stock'] //qty
                        )
                    );
                    $import++;
                    try {
                        //$this->tierPrice->add($sku, $tierPriceData);

                        $sku = $_product->getSku();

                        //$_product->save(); // remove the customizable options
                        $this->_productRepository->save($_product);

                        $adapter->delete($table, $adapter->quoteInto('sku IN (?)', $sku));

                        /*$returnData[] = array(
                            "article_code"=>$regular_price['product_sku'],
                            "status"=>1,
                            "error_code"=>"Article stock Updated.",
                        );*/
                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                        /*$returnData[] = array(
                            "article_code"=>$regular_price['product_sku'],
                            "status"=>0,
                            "error_code"=>$e->getMessage(),
                        );*/
                        $not_exists.='<br />' . __('SKU') . ' <b>"' . $regular_price['product_sku'] . '"</b> ';
                    }

            } catch (\Exception $e) {
                //$this->messageManager->addError($e->getMessage());
                /*$returnData[] = array(
                    "article_code"=>$regular_price['product_sku'],
                    "status"=>0,
                    "error_code"=>"Article not found.",
                );*/
                $not_exists.='<br />' . __('SKU') . ' <b>"' . $regular_price['product_sku'] . '"</b> ';
            }

        }



        if ($import > 0) {
            $this->logger->log('ERROR','Inventory imported successfully:',[$not_exists]);
        } else {
            $this->logger->log('ERROR','No Inventory imported:',[$not_exists]);
        }
        //echo '<pre>'.print_r($returnData, true).'</pre>'; exit();

	}
}
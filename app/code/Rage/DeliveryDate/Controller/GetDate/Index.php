<?php
namespace Rage\DeliveryDate\Controller\GetDate;

class Index extends \Magento\Framework\App\Action\Action
{

	protected $_date;

    protected $timezone;

    protected $_cart;

    protected $_productRepository;

    protected $_resultJsonFactory;

	public function __construct(
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
        $this->_date 				= $date;
        $this->timezone             = $timezone;
        $this->_cart                = $cart;
        $this->_productRepository   = $productRepository;
        $this->_resultJsonFactory   = $resultJsonFactory;
		$this->_pageFactory 		= $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{

        $json = [];

        $delivery_method = $this->getRequest()->getParam('delivery_method');

        //echo 'delivery_method: '.$delivery_method;

        if($delivery_method != ''){

            $has_cold_chain_product = 0;
            $has_regular_product = 0;
            $has_both = 0;

            // get quote items array
            $items = $this->_cart->getQuote()->getAllItems();

            foreach($items as $item) {
                //echo 'ID: '.$item->getProductId().'<br />';
                $_product = $this->_productRepository->getById($item->getProductId());
                if($_product->getColdChain() == 1){
                    $has_cold_chain_product = 1;
                } else {
                    $has_regular_product++;
                }
            }

            //echo 'has_regular_product: '.$has_regular_product;
            //echo 'has_cold_chain_product: '.$has_cold_chain_product;



            if($has_regular_product > 0 && $has_cold_chain_product){
                $has_both = 1;
            }

            if($delivery_method == 'all'){

                $rdd_standard_text = '';
                $rdd_express_text = '';
                $regular_max_shipping_date = '';
                $express_max_shipping_date = '';

                //echo $this->_date->date('Y-m-d H:i:s');
                //echo $this->timezone->formatDate($this->_date->date('Y-m-d H:i:s'), true, true);
                //echo $this->timezone->date($this->_date->date('Y-m-d H:i:s'))->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

                $current_date = $this->timezone->date($this->_date->date('Y-m-d H:i:s'))->format('Y-m-d H:i:s');
                $server_current_datetime = $this->timezone->date($this->_date->date('Y-m-d H:i:s'))->format('Y-m-d H:i:s');

                //echo $current_date;

                $current_date = strtotime($current_date);
                //echo $current_date;
                //$current_date = strtotime("2021-05-27 14:55:00"); //example as today is 2016-03-25
                //$current_date = strtotime("2021-05-27 15:55:00"); //example as today is 2016-03-25

                $holidayDates = [];
                $count7WD = 0;
                $temp = $current_date;
                if($has_regular_product == 0 && $has_cold_chain_product == 1) {

                    $hours = date("H", $temp);
                    $min = date("i", $temp);
                    $day_number = date("w", $temp);

                    //echo $day_number .' - '.$hours.' - '.$min;

                    if($day_number == 5 || $day_number == 6 || $day_number == 0){
                        $next1WD = strtotime('+2 weekday', $temp);
                        $temp = $next1WD;
                    } else if($day_number == 4 && $hours >=15){
                        $next1WD = strtotime('+3 weekday', $temp);
                        $temp = $next1WD;
                    } else {
                        if($hours >=15) {
                            $next1WD = strtotime('+2 weekday', $temp);
                            $temp = $next1WD;
                        } else {
                            $next1WD = strtotime('+1 weekday', $temp);
                            $temp = $next1WD;
                        }
                    }

                    $dd_regular = date("D, d M", $temp);
                    $regular_max_shipping_date = date("m/d/Y", $temp);

                } else {
                    while($count7WD < 7){
                        $next1WD = strtotime('+1 day', $temp);
                        //$day_number = date("w", $next1WD);
                        //echo $day_number.'-';
                        //if($day_number !=6 && $day_number !=0){
                            $next1WDDate = date('Y-m-d', $next1WD);
                            if(!in_array($next1WDDate, $holidayDates)){
                                $count7WD++;
                            }
                        //}
                        $temp = $next1WD;

                        //echo $count7WD.'<br />';
                    }
                    $day_number = date("w", $temp);
                    if($day_number == 6){
                        $temp = strtotime('+2 day', $temp);
                    } else if($day_number == 0){
                        $temp = strtotime('+1 day', $temp);
                    }
                    $dd_regular = date("D, d M", $temp);
                    $regular_max_shipping_date = date("m/d/Y", $temp);
                }

                //--------------------------------------------------------------

                $count1WD = 0;
                $temp = $current_date; //example as today is 2016-03-25

                $hours = date("H", $temp);
                $min = date("i", $temp);
                $day_number = date("w", $temp);

                //echo $day_number .' - '.$hours.' - '.$min;

                if($day_number == 5 || $day_number == 6 || $day_number == 0){
                    $next1WD = strtotime('+2 weekday', $temp);
                    $temp = $next1WD;
                } else if($day_number == 4 && $hours >=15){
                    $next1WD = strtotime('+3 weekday', $temp);
                    $temp = $next1WD;
                } else {
                    if($hours >=15) {
                        $next1WD = strtotime('+2 weekday', $temp);
                        $temp = $next1WD;
                    } else {
                        $next1WD = strtotime('+1 weekday', $temp);
                        $temp = $next1WD;
                    }
                }

                $dd_cold_storage = date("D, d M", $temp);
                $dd_cold_storage_date = date("m/d/Y", $temp);
                $dd_cold_date_integer = $temp;


                $temp = $current_date; //example as today is 2016-03-25

                $hours = date("H", $temp);
                $day_number = date("w", $temp);

                //echo $day_number .' - '.$hours;

                if($day_number == 6 || $day_number == 0){
                    $next1WD = strtotime('+2 weekday', $temp);
                    $temp = $next1WD;
                } else if($day_number == 5 && $hours >=15){
                    $next1WD = strtotime('+2 weekday', $temp);
                    $temp = $next1WD;

                } else {
                    if($hours >=15) {
                        $next1WD = strtotime('+2 weekday', $temp);
                        $temp = $next1WD;
                    } else {
                        $next1WD = strtotime('+1 weekday', $temp);
                        $temp = $next1WD;
                    }
                }

                $express_shipping_date = date("D, d M", $temp);
                $express_shipping_date_integer = $temp;
                $express_max_shipping_date = date("m/d/Y", $temp);

                if($has_both == 1){
                    $rdd_standard_text = $dd_cold_storage.' - '.$dd_regular;
                    if($express_shipping_date_integer == $dd_cold_date_integer) {
                        $rdd_express_text = $dd_cold_storage;
                    } else {

                        if($express_shipping_date_integer > $dd_cold_date_integer) {
                            $rdd_express_text = $dd_cold_storage.' - '.$express_shipping_date;
                        } else {
                            $rdd_express_text = $express_shipping_date.' - '.$dd_cold_storage;
                        }
                        //$rdd_express_text = $dd_cold_storage.' - '.$express_shipping_date;
                    }
                } else {
                    $rdd_standard_text = $dd_regular;
                    $rdd_express_text = $express_shipping_date;
                }

                $json = [
                    'success' => true,
                    'has_both' => $has_both,
                    'rdd_cold_storage' => $dd_cold_storage,
                    'rdd_regular' => $dd_regular,
                    'rdd_express' => $express_shipping_date,
                    'rdd_standard_text' => $rdd_standard_text,
                    'rdd_express_text' => $rdd_express_text,
                    'rdd_cold_storage_date' => $dd_cold_storage_date,
                    'rdd_standard_max_date' => $regular_max_shipping_date,
                    'rdd_express_max_date' => $express_max_shipping_date,
                    'server_current_datetime' => $server_current_datetime,
                    'type' => 'all',
                    'msg' => ''
                ];

                //echo '<pre>'.print_r($json, true).'</pre>';

            } else if($delivery_method == 'standardshipping'){

                $holidayDates = [];

                $count7WD = 0;
                $temp = strtotime(date("Y-m-d")); //example as today is 2016-03-25
                while($count7WD < 7){
                    $next1WD = strtotime('+1 weekday', $temp);
                    $next1WDDate = date('Y-m-d', $next1WD);
                    if(!in_array($next1WDDate, $holidayDates)){
                        $count7WD++;
                    }
                    $temp = $next1WD;
                }

                $next5WD = date("m/d/Y", $temp);

                //echo $next5WD; //if today is 2016-03-25 then it will return 2016-04-06 as many days between are holidays

                $json = [
                    'success' => true,
                    'rdd' => $next5WD,
                    'type' => 'standardshipping',
                    'msg' => ''
                ];

            } else if($delivery_method == 'expressshipping'){

                $holidayDates = [];

                $count1WD = 0;
                $temp = strtotime(date("Y-m-d")); //example as today is 2016-03-25
                while($count1WD < 1){
                    $next1WD = strtotime('+1 weekday', $temp);
                    $next1WDDate = date('Y-m-d', $next1WD);
                    if(!in_array($next1WDDate, $holidayDates)){
                        $count1WD++;
                    }
                    $temp = $next1WD;
                }

                $nextWD = date("m/d/Y", $temp);

                //echo $next5WD; //if today is 2016-03-25 then it will return 2016-04-06 as many days between are holidays

                $json = [
                    'success' => true,
                    'rdd' => $nextWD,
                    'type' => 'expressshipping',
                    'msg' => ''
                ];

            }

            /*$holidayDates = array(
                '2016-03-26',
                '2016-03-27',
                '2016-03-28',
                '2016-03-29',
                '2016-04-05',
            );*/
            /*$holidayDates = [];

            $count7WD = 0;
            $temp = strtotime(date("Y-m-d")); //example as today is 2016-03-25
            while($count7WD < 7){
                $next1WD = strtotime('+1 weekday', $temp);
                $next1WDDate = date('Y-m-d', $next1WD);
                if(!in_array($next1WDDate, $holidayDates)){
                    $count7WD++;
                }
                $temp = $next1WD;
            }

            $next5WD = date("m/d/Y", $temp);

            //echo $next5WD; //if today is 2016-03-25 then it will return 2016-04-06 as many days between are holidays

            $json = [
                'success' => true,
                'rdd' => $next5WD,
                'msg' => ''
            ];*/

        } else {
            $json = [
                'success' => false,
                'msg' => 'please choose a delivery method'
            ];
        }

		$result = $this->_resultJsonFactory->create();
        $result->setData($json);
        return $result;

	}

}
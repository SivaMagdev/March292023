<?php
namespace Ecomm\Sap\Controller\Customer;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	protected $_resultJsonFactory;

    protected $storeManager;

	protected $_customermaster;

    protected $_orderRepository;

    protected $_customerRepository;

    protected $_addressRepository;

    protected $_dataAddressFactory;

    private $_loggerFactory;

    protected $_helper;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ecomm\Sap\Model\CustomerMaster $customermaster,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $dataAddressFactory,
        \Ecomm\Sap\Model\LoggerModel $loggerFactory,
		\Ecomm\Sap\Helper\Data $helper
	)
	{
		$this->_pageFactory 		= $pageFactory;
        $this->_resultJsonFactory 	= $resultJsonFactory;
        $this->storeManager         = $storeManager;
        $this->_customermaster      = $customermaster;
        $this->_orderRepository     = $orderRepository;
        $this->_customerRepository     = $customerRepository;
        $this->_addressRepository       = $addressRepository;
        $this->_dataAddressFactory  = $dataAddressFactory;
        $this->_loggerFactory       = $loggerFactory;
        $this->_helper 				= $helper;
		return parent::__construct($context);
	}

	public function execute()
	{
		//echo "Sap getstock World";

		//echo $this->_helper->getMode().'<br />';
		//echo $this->_helper->getDevelopmentUrl().'<br />';
		//echo $this->_helper->getDevUsername().'<br />';
		//echo $this->_helper->getDevPassword().'<br />';
		//echo $this->_helper->getLiveUrl().'<br />';
		//echo $this->_helper->getUsername().'<br />';
		//echo $this->_helper->getPassword().'<br />';

		$results = [];
		$email = $this->getRequest()->getParam('email');

        echo $email.'<br />';

		if($email != '' ){

			/*$storeId = $this->storeManager->getStore()->getId();
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            echo 'websiteId: '.$websiteId.'<br />';
			$customer_info = $this->_customerRepository->get($email, $websiteId);

			echo 'Customer ID: '.$customer_info ->getId().'<br />';

			if ($customer_info->getAddresses()) {

                //echo '<pre>'.print_r($billing_address_data, true).'</pre>';
                $billingAddressId = $customer_info->getDefaultBilling();
                echo 'billingAddressId: '.$billingAddressId.'<br />';

                $address = $this->_addressRepository->getById($billingAddressId);

                $address->setCity('test city-1');

                $this->_addressRepository->save($address);

            }*/
			//$postData = $this->_customermaster->getCustomerData($customer_id);

            //echo '<pre>'.print_r($postData, true).'</pre>';


			//$this->_loggerFactory->createLog('CustomerSalesOrder:Req: '.json_encode($postData));

            /*if($postData) {

                $postData = json_encode($postData);

                echo $postData;
                exit();

            } else {
                 $this->_loggerFactory->createLog('OrderStatusSAP:Res: Post Data is Empty');
            }*/

        }

	}

    public function checkConnection( $url ) {
	    $timeout = 10;
	    $ch = curl_init();
	    curl_setopt ( $ch, CURLOPT_URL, $url );
	    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	    curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout );
	    $http_respond = curl_exec($ch);
	    $http_respond = trim( strip_tags( $http_respond ) );
	    $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	    if ( ( $http_code == "200" ) || ( $http_code == "302" ) ) {
	       return true;
	    } else {
	        // return $http_code;, possible too
	    	return false;
	    }
	    curl_close( $ch );
    }
}
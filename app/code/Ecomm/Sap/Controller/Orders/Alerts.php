<?php
namespace Ecomm\Sap\Controller\Orders;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;

class Alerts extends \Magento\Framework\App\Action\Action
{
	const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $_helper;

    protected $_request;

    protected $storeManager;

	protected $_date;

    protected $_orderCollectionFactory;

	protected $_resultJsonFactory;

	public function __construct(
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Layout $layout,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{

        $this->_transportBuilder        = $transportBuilder;
        $this->inlineTranslation        = $inlineTranslation;
        $this->scopeConfig              = $scopeConfig;
        $this->_helper                  = $helper;
        $this->_request                 = $request;
        $this->storeManager             = $storeManager;
        $this->_date 				    = $date;
        $this->_orderCollectionFactory  = $orderCollectionFactory;
        $this->_resultJsonFactory 	    = $resultJsonFactory;
		$this->_pageFactory 		    = $pageFactory;
        $this->layout = $layout;
		return parent::__construct($context);
	}

	public function execute()
	{
        $orderSubmittedCollection = $this->getSubmittedOrderCollection();
        $orderCreatedCollection = $this->getCreatedOrderCollection();

        //echo $orderSubmittedCollection->getSelect();
        //echo $orderCreatedCollection->getSelect();

        //echo count($orderSubmittedCollection);

        if(count($orderSubmittedCollection) > 0){

            try {

                $this->inlineTranslation->suspend();

                $items = [];

                //echo $this->_helper->emailPriceExpiryTemplate();

                foreach($orderSubmittedCollection as $order){

                    $data = [
                        'order_number' => $order->getIncrementId(),
                        'created_at' => $order->getCreatedAt(),
                        'status' => $order->getStatusLabel()
                    ];
                    $items[] = $data;

                }

                //echo '<pre>'.print_r($items, true).'</pre>'; //exit();

                $itemsHtml = '';

                if ($items) {
                    $itemsHtml .= '<table class="details" cellpadding="5" cellspacing="2" border="1">';
                        $itemsHtml .= '<tr>';
                            $itemsHtml .= '<th>Order ID</th>';
                            $itemsHtml .= '<th>Created At</th>';
                            $itemsHtml .= '<th>Status</th>';
                        $itemsHtml .= '</tr>';
                        foreach ($items as $item) {
                            $itemsHtml .= '<tr>';
                                $itemsHtml .= '<td>'.$item['order_number'].'</td>';
                                $itemsHtml .= '<td>'.$item['created_at'].'</td>';
                                $itemsHtml .= '<td>'.$item['status'].'</td>';
                            $itemsHtml .= '</tr>';
                        }
                    $itemsHtml .= '</table>';
                }

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $sender = [
                    'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope)
                ];

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

                $to_emails = explode(',', $this->_helper->getToEmails());

                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($this->_helper->emailOrderProcessingFailedTemplate())
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                        ]
                    )
                    ->setTemplateVars(['itemsHtml' => $itemsHtml])
                    ->setFrom($sender)
                    ->addTo($to_emails)
                    ->getTransport();

                $transport->sendMessage();

                $this->inlineTranslation->resume();

                echo 'Order Not processed Notification sent.';
            } catch (\Exception $e) {
                echo $e->getMessage();
            }

        }

        //echo count($orderSubmittedCollection);
        //echo count($orderCreatedCollection);

        if(count($orderCreatedCollection) > 0){

            try {

                $this->inlineTranslation->suspend();
                $items = [];

                //echo $this->_helper->emailPriceExpiryTemplate();

                foreach($orderCreatedCollection as $order){

                    $data = [
                        'order_number' => $order->getIncrementId(),
                        'created_at' => $order->getCreatedAt(),
                        'status' => $order->getStatusLabel()
                    ];
                    $items[] = $data;

                }

                //echo '<pre>'.print_r($items, true).'</pre>'; //exit();

                $itemsHtml = '';

                if ($items) {
                    $itemsHtml .= '<table class="details" cellpadding="5" cellspacing="2" border="1">';
                        $itemsHtml .= '<tr>';
                            $itemsHtml .= '<th>Order ID</th>';
                            $itemsHtml .= '<th>Created At</th>';
                            $itemsHtml .= '<th>Status</th>';
                        $itemsHtml .= '</tr>';
                        foreach ($items as $item) {
                            $itemsHtml .= '<tr>';
                                $itemsHtml .= '<td>'.$item['order_number'].'</td>';
                                $itemsHtml .= '<td>'.$item['created_at'].'</td>';
                                $itemsHtml .= '<td>'.$item['status'].'</td>';
                            $itemsHtml .= '</tr>';
                        }
                    $itemsHtml .= '</table>';
                }

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $sender = [
                    'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope)
                ];

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

                $to_emails = explode(',', $this->_helper->getToEmails());

                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($this->_helper->emailAckFailedTemplate())
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                        ]
                    )
                    ->setTemplateVars(['itemsHtml' => $itemsHtml])
                    ->setFrom($sender)
                    ->addTo($to_emails)
                    ->getTransport();

                $transport->sendMessage();

                $this->inlineTranslation->resume();

                echo 'ACK Not processed Notification sent.';
            } catch (\Exception $e) {
                echo $e->getMessage();
            }

        }

	}

    public function getOrderCollection() {
        $collection = $this->_orderCollectionFactory->create()
         ->addAttributeToSelect('*')
         ->addFieldToFilter($field, $condition); //Add condition if you wish
    }

    public function getSubmittedOrderCollection() {

        //echo $this->_date->date('Y-m-d H:i:s').'<br />';

        $today_date = strtotime($this->_date->date('Y-m-d H:i:s'));
        $today_date = $today_date - (15 * 60);
        $date = date("Y-m-d H:i:s", $today_date);

        $order_from_date = strtotime("-2 day", $today_date);

        $order_from_date = date("Y-m-d H:i:s", $order_from_date);

        //echo $date .' - '. $order_from_date;

        return $this->_orderCollectionFactory->create()
         ->addFieldToSelect('*')
         //->addFieldToFilter('created_at', ['gteq' => $date])
         ->addFieldToFilter('created_at', ['lt' => $date])
         ->addFieldToFilter('created_at', ['gteq' => $order_from_date])
         ->addFieldToFilter('batch_id', ['null' => true])
         //->addFieldToFilter('sap_id', ['null' => true])
         //->getFirstItem()
         //->setPageSize(10)
         ->setOrder('created_at','ASC');

    }

    public function getCreatedOrderCollection() {

        //echo $this->_date->date('Y-m-d H:i:s').'<br />';

        $today_date = strtotime($this->_date->date('Y-m-d H:i:s'));
        $today_date = $today_date - (15 * 60);
        $date = date("Y-m-d H:i:s", $today_date);

        $order_from_date = strtotime("-2 day", $today_date);

        $order_from_date = date("Y-m-d H:i:s", $order_from_date);

        //echo $date .' - '. $order_from_date;

        return $this->_orderCollectionFactory->create()
         ->addFieldToSelect('*')
         //->addFieldToFilter('created_at', ['gteq' => $date])
         ->addFieldToFilter('created_at', ['lt' => $date])
         ->addFieldToFilter('created_at', ['gteq' => $order_from_date])
         ->addFieldToFilter('batch_id', ['notnull' => true])
         ->addFieldToFilter('sap_id', ['null' => true])
         //->setPageSize(10)
         ->setOrder('created_at','ASC');

    }

}
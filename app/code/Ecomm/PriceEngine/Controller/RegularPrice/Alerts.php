<?php
namespace Ecomm\PriceEngine\Controller\RegularPrice;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;
use Ecomm\PriceEngine\Model\RegularPriceFactory;

class Alerts extends \Magento\Framework\App\Action\Action
{
	const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    const PRICE_EXPIRY_ITEMS_ALERT_TEMPLATE_FILE =
                    'Ecomm_Notification::notifications/price_expiry_items.phtml';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $_helper;

    protected $_request;

    protected $storeManager;

    protected $_regularpriceFactory;

	protected $_date;

    protected $_productFactory;

    protected $_productRepository;

    protected $_customerGroup;

	protected $_resultJsonFactory;

    protected $_productCollectionFactory;

	public function __construct(
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        RegularPriceFactory $regularpriceFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
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
        $this->_regularpriceFactory     = $regularpriceFactory;
        $this->_date 				= $date;
        $this->_productFactory  	= $productFactory;
        $this->_productRepository 	= $productRepository;
        $this->_customerGroup     	= $customerGroup;
        $this->_resultJsonFactory 	= $resultJsonFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
		$this->_pageFactory 		= $pageFactory;
        $this->layout = $layout;
		return parent::__construct($context);
	}

	public function execute()
	{

        $today_date = strtotime($this->_date->date('Y-m-d'));
        $notification_date = strtotime("+5 day", $today_date);

        $expire_date = date('Y-m-d', $notification_date);

        //echo $expire_date.'<br />';

        $collection = $this->_productCollectionFactory->create()
        ->addAttributeToSelect('*')
        ->addFieldToFilter('price_effective_to', ['lteq' => $expire_date])
        ->addFieldToFilter('price_effective_to', ['gteq' => $today_date]);

        //echo 'Record Count: '.count($collection); exit();
        if(count($collection) > 0){

            try {

                //$this->inlineTranslation->suspend();

                $items = [];

                //echo $this->_helper->emailPriceExpiryTemplate();

                foreach($collection as $product){

                    $data = [
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'price_effective_to' => date("Y-m-d", strtotime($product->getPriceEffectiveTo()))
                    ];
                    $items[] = $data;

                }

                //echo '<pre>'.print_r($items, true).'</pre>'; //exit();

                $itemsHtml = '';

                /*$ItemsBlock = $this->layout->createBlock(Template::class)
                    ->setTemplate(self::PRICE_EXPIRY_ITEMS_ALERT_TEMPLATE_FILE)
                    ->setData('items', $items);

                $itemsHtml = $ItemsBlock->toHtml();*/

                if ($items) {
                    $itemsHtml .= '<table class="details" cellpadding="5" cellspacing="2" border="1">';
                        $itemsHtml .= '<tr>';
                            $itemsHtml .= '<th>Product Name</th>';
                            $itemsHtml .= '<th>SKU</th>';
                            $itemsHtml .= '<th>Expiry Date</th>';
                        $itemsHtml .= '</tr>';
                        foreach ($items as $item) {
                            $itemsHtml .= '<tr>';
                                $itemsHtml .= '<td>'.$item['name'].'</td>';
                                $itemsHtml .= '<td>'.$item['sku'].'</td>';
                                $itemsHtml .= '<td>'.$item['price_effective_to'].'</td>';
                            $itemsHtml .= '</tr>';
                        }
                    $itemsHtml .= '</table>';
                }

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $sender = [
                    'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                ];

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

                $to_emails = explode(',', $this->_helper->getToEmails());

                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($this->_helper->emailPriceExpiryTemplate())
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

                //$this->inlineTranslation->resume();

                echo 'Price Expiry Notification sent.';
            } catch (\Exception $e) {
                //echo $e->getMessage();
            }

        } else {
            echo 'Not Data found';
        }

	}

}
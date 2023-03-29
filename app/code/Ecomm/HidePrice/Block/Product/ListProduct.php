<?php

namespace Ecomm\HidePrice\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\Element;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Helper\Output as OutputHelper;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
	    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = Toolbar::class;

    /**
     * Product Collection
     *
     * @var AbstractCollection
     */
    protected $_productCollection;

    /**
     * Catalog layer
     *
     * @var Layer
     */
    protected $_catalogLayer;

    /**
     * @var PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var Data
     */
    protected $urlHelper;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

	public $getSalableQuantityDataBySku;
	public $customerSession;
	public $customerRepository;
	public $eavConfig;

    /**
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $urlHelper
     * @param array $data
     * @param OutputHelper|null $outputHelper
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        array $data = [],
        ?OutputHelper $outputHelper = null,
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
 		\Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_catalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;
        $data['outputHelper'] = $outputHelper ?? ObjectManager::getInstance()->get(OutputHelper::class);
    	$this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
    	$this->customerSession = $customerSession;
    	$this->customerRepository = $customerRepository;
    	$this->eavConfig = $eavConfig;
    	$this->scopeConfig = $scopeConfig;
 		$this->storeManager = $storeManager;

        parent::__construct(
            $context,
            $postDataHelper,
        	$layerResolver,
        	$categoryRepository,
        	$urlHelper,
        	$data,
        	$outputHelper
        );
    }

    public function getConfigValue($configParam) {
		//return $this->scopeConfig->getValue("sectionId/groupId/fieldId",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->storeManager->getStore()->getStoreId();
		return $this->scopeConfig->getValue($configParam,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->storeManager->getStore()->getStoreId());
	}

	public function getIsLoggedIn(){

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$customerSession = $objectManager->create(\Magento\Customer\Model\Session::class);
		if ($customerSession->isLoggedIn()) {
			return true;
		} else {
			return false;
		}
	}

    public function getIsApproved(){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerRepository = $objectManager->get('\Magento\Customer\Api\CustomerRepositoryInterface');
        $customerSession = $objectManager->create(\Magento\Customer\Model\Session::class);
        $_eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

        //echo 'Customer ID:'.$customerSession->getId();
        //exit();

        if($customerSession->getId()){
        	$customerData= $customerRepository->getById($customerSession->getId());

        	$application_status = 0;

        	if($customerData->getCustomAttribute('application_status')){
	            $application_status = $customerData->getCustomAttribute('application_status')->getValue();
	        }

	        //echo 'application_status: '.$application_status;

	        $attribute = $_eavConfig->getAttribute('customer', 'application_status');
	        $options = $attribute->getSource()->getAllOptions();
	        $application_statuses = [];
	        foreach ($options as $option) {
	            if ($option['value'] > 0) {
	                $application_statuses[$option['value']] = $option['label'];
	            }
	        }

	        //echo '<pre>'.print_r($application_statuses, true).'</pre>'; exit();
	        $approved_id = array_search("Approved",$application_statuses);

	        //echo 'approved_id: '.$approved_id; exit();
	        //echo $application_status.'-'.$approved_id;

	        if($approved_id == $application_status){

	        	//echo 'true';
	            return true;
	        } else {
	        	//echo 'false';
	            return false;
	        }

	        //exit();

        } else {
        	return false;
        }

        /**/
    }

}
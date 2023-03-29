<?php
namespace Ecomm\HidePrice\Plugin\Model;

class Product
{

	protected $date;

    protected $customerSession;

    protected $customerRepository;

    protected $_customerGroup;

    protected $regularpriceFactory;

	public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Ecomm\PriceEngine\Model\RegularPriceFactory $regularpriceFactory
    )
    {
    	$this->date                = $date;
    	$this->customerSession      = $customerSession;
        $this->customerRepository   = $customerRepository;
        $this->customerGroup       = $customerGroup;
        $this->regularpriceFactory = $regularpriceFactory;
	}

    public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result)
    {

    	$customerId = $this->customerSession->getCustomer()->getId();

    	if($customerId > 0){

    		//echo 'SKU: '.$subject->getSku().'<br />';
    		//echo 'ID: '.$subject->getId().'<br />';

    		$customerData= $this->customerRepository->getById($customerId);


    		//echo 'Material: '.print_r($product_info->getMaterial(), true);

    		//echo '<pre>'.print_r($customerData->getGroupId(), true).'</pre>'; exit();

    		$customer_groups = $this->getCustomerGroups();
    		if($customerData->getCustomAttribute('disproportionate_hospital')){
    			$disproportionate_hospital = $customerData->getCustomAttribute('disproportionate_hospital')->getValue();

    			if($disproportionate_hospital == 1){
    				//echo 'Dis Hos: '.$customerData->getCustomAttribute('disproportionate_hospital')->getValue().'<br />';

    				$today_date = $this->date->date('Y-m-d');

    				$_regular_price_collections = $this->regularpriceFactory->create()->getCollection()
    				->addFieldToFilter('ndc', $subject->getSku())
    				->addFieldToFilter('start_date', ['lteq' => $today_date])
    				->addFieldToFilter('end_date', ['gteq' => $today_date])
    				->getFirstItem()
    				->setOrder('updated_at','desc');

    				if($_regular_price_collections->getDishPrice() > 0){
    					//echo 'Collections: '.print_r($_regular_price_collections->getData(), true);
    					return $_regular_price_collections->getDishPrice();
    				}
    			}
    		}

    		return $result;

    	} else {
    		return $result;
    	}
    }

    /**
     * Get customer groups
     *
     * @return array
     */
    private function getCustomerGroups() {
        $groups = [];
        $customerGroups = $this->customerGroup->toOptionArray();
        foreach($customerGroups as $customerGroup){
            $groups[$customerGroup['value']] = $customerGroup['label'];
        }
        return $groups;
    }
}
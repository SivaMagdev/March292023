<?php
namespace Ecomm\Theme\Block;


class AwardsSection extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    protected $customerSession;

    protected $customerRepository;


    public function __construct(
    	\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteriaInterface,
        array $data = []
    ) {

        $this->customerSession      = $customerSession;
        $this->customerRepository   = $customerRepository;
        $this->searchCriteriaInterface   = $searchCriteriaInterface;

        parent::__construct($context);
	}

    public function getRewardsList()
    {
        die('hello');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $rewards = $objectManager->create('Ecomm\Rewards\Api\RewardsRepositoryInterface')->getList($searchCriteriaBuilder->create());
        return $rewards;
    }
}
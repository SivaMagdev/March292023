<?php
 
namespace Ecomm\HinEligibilityCheck\Block;
 
use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Ecomm\PriceEngine\Model\ResourceModel\ExclusivePrice\CollectionFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
 
class exclusivePrice extends Template
{
    
    /**
     * @var CollectionFactory
     */
    public $collection;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;
 
    public function __construct(Context $context, CollectionFactory $collectionFactory, AddressRepositoryInterface $addressRepository, array $data = [])
    {
        $this->collection = $collectionFactory;
        $this->addressRepository = $addressRepository;
        parent::__construct($context, $data);
    }
 
    public function getCollection()
    {
        return $this->collection->create();
    }

    public function getAddressCollection()
    {
        return $this->addressRepository;
    }
 
}
<?php
namespace Ecomm\Servicerequest\Ui\Component;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\RequestInterface;


class Customer implements OptionSourceInterface
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $_customerCollectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $customerTree;

    /**
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param RequestInterface $request
     */
    public function __construct(
        CollectionFactory $customerCollectionFactory,
        RequestInterface $request
    ) {
        $this->_customerCollectionFactory = $customerCollectionFactory;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getCustomerTree();
    }

    /**
     * Retrieve categories tree
     *
     * @return array
     */
    protected function getCustomerTree()
    {
        if ($this->customerTree === null) {
            $collection = $this->_customerCollectionFactory->create();
            //$collection->addAttributeToSelect('*');
            // $collection->addNameToSelect();

            foreach ($collection as $customer) {
                $customerId = $customer->getEntityId();
                if (!isset($productById[$customerId])) {
                    $customerById[$customerId] = [
                        'value' => $customerId
                    ];
                }
                $customerById[$customerId]['label'] = $customer->getFirstname().' '.$customer->getLastname();
            }
            $this->customerTree = $customerById;
        }
        return $this->customerTree;
    }
}
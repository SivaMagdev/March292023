<?php
namespace Ecomm\AdminCatalog\Model\Adminhtml\System\Config\Source\Customer;

class Group implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(\Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory)
    {
        $this->_groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
    	$this->_options = '';
        if (empty($this->_options)) {
            $this->_options = $this->_groupCollectionFactory->create()->loadData()->toOptionArray();
        }
        return $this->_options;
    }
}
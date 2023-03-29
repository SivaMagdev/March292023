<?php
 
namespace Ecomm\OrderHin\Block;
 
use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Ecomm\OrderHin\Model\ResourceModel\HinData\CollectionFactory;
 
class HinDataCollection extends Template
{
    
    /**
     * @var CollectionFactory
     */
    public $collection;
 
    public function __construct(Context $context, CollectionFactory $collectionFactory, array $data = [])
    {
        $this->collection = $collectionFactory;
        parent::__construct($context, $data);
    }
 
    public function getHinCollection()
    {
        return $this->collection->create();
    }
 
}
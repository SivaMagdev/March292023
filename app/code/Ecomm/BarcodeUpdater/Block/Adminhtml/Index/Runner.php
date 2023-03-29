<?php

/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_HinValidator
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\BarcodeUpdater\Block\Adminhtml\Index;

use Magento\Backend\Block\Template\Context;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;

/**
 * Index block class
 */
class Runner extends \Magento\Backend\Block\Template
{
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepositoryInterface;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param StoreRepositoryInterface $storeRepository
     * @param CollectionFactory $addressCollectionFactory
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param ProductRepository $productRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreRepositoryInterface $storeRepository,
        CollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepositoryInterface,
        ProductRepository $productRepository,
        array $data = []
    ) {
        $this->storeRepository = $storeRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    /**
     * To get store list
     *
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    public function storelist()
    {
        return $this->storeRepository->getList();
    }

    /**
     * To Run HIN Validation
     *
     * @return \Magento\Store\Model\ResourceModel\Website\Collection
     */
    public function getBarcodeUpdater()
    {
        $results = [];
        $collection = $this->productCollectionFactory->create();
        if ($collection->getSize() > 0) {  
            $data = [];         
            foreach ($collection as $product) {
                $result = [];
                $result['id'] = $product->getId();
                $productData =  $this->productRepository->getById($product->getId());
                $result['sku'] = $productData->getSku();
                $result['ndc'] = $productData->getNdc();

                $productData->setBarcode(
                    $this->barCodeFormate(str_replace('-','',trim($productData->getSku())))
                );
                try{
                    $this->productRepositoryInterface->save($productData);
                    $result['barcode'] = $productData->getbarcode();
                    $result['status'] = 'Success'; 
                }catch(Exception $e){
                    $result['status'] = 'Faild'; 
                }
              array_push($data, $result);
            }
            $results['count'] =  $collection->getSize();
            $results['data'] =$data;
        }
        return $results;
    }
    /**
     * Barcode sku formater
     *
     * @param string $sku
     * @return string
     */
    private function barCodeFormate($sku){
        if(strlen($sku) > 10){
            $arr = [];
            $arr = str_split($sku);
            unset($arr[5]);
            return implode('', $arr);
        }
        return $sku;
    }
}

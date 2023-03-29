<?php
namespace Ecomm\Sap\Model;

use Ecomm\Sap\Api\ProductMasterInterface;

class ProductMaster implements ProductMasterInterface
{
	/**
     * Return the sum of the two numbers.
     *
     * @api
     * @param int $num1 Left hand operand.
     * @param int $num2 Right hand operand.
     * @return int The sum of the two values.
     */
    protected $_dataFactory;

    public $_request;

    protected $_productFactory;

    protected $_productRepository;

    protected $_productCollectionFactory;

    /**
     * @var Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $_stockStateInterface;

    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    protected $_loggerFactory;

    protected $_logger;

	public function __construct(
        \Ecomm\Sap\Api\Data\ProductStockOutdataInterfaceFactory $dataFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\CatalogInventory\Api\StockStateInterface $stockStateInterface,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Ecomm\Sap\Model\LoggerModel $loggerFactory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_dataFactory = $dataFactory;
        $this->_request         = $request;
        $this->_productFactory  = $productFactory;
        $this->_productRepository = $productRepository;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_stockStateInterface = $stockStateInterface;
        $this->_stockRegistry = $stockRegistry;
        $this->_loggerFactory  = $loggerFactory;
        $this->_logger          = $logger;
    }

	public function getArticleDetails() {

        $returnData = [];

        $this->_loggerFactory->createLog('ProductMasterRequest: '.$this->_request->getContent());

        //$this->_logger->critical('ProductStock', ['data' => $this->_request->getContent()]);

        $requestData = json_decode($this->_request->getContent());

        if($requestData->MaterialDetails){
            //echo '<pre>'.print_r($requestData->MaterialDetails, true).'</pre>';
            foreach($requestData->MaterialDetails as $productData){

                //echo 'Count'.count($collection);

                //echo $this->formatSKU($productData->EANUPC);
                //exit();

                $collection = $this->_productCollectionFactory->create();
                $collection->addAttributeToSelect('material');
                $collection->addFieldToFilter('material', ['eq' => $productData->MaterialCode]);

                if(count($collection) > 0){
                    try {
                        $product_data = $collection->getFirstItem();
                        //echo '<pre>'.print_r($product_data['entity_id'], true).'</pre>';
                        $product = $this->_productFactory->create()->load($product_data['entity_id']);

                        $product->setMaterial(trim($productData->MaterialCode));
                        $product->setMaterialDesc(trim($productData->MaterialDescription));
                        $product->setEanUpc(trim($productData->EANUPC));
                        $product->setDivisionCode(trim($productData->Division));
                        $product->setDivisionDesc(trim($productData->DivisonDescription));
                        $product->setProductHierarchy(trim($productData->ProductHierarchy));
                        $product->setTheraphyCode(trim($productData->Theraphy));
                        $product->setTheraphyDesc(trim($productData->TheraphyDesc));
                        $product->setSubTheraphyCode(trim($productData->SubTheraphy));
                        $product->setSubTheraphyDesc(trim($productData->SubTheraphyDesc));
                        $product->setMolecule(trim($productData->Molecule));
                        $product->setMoleculeDesc(trim($productData->MoleculeDesc));
                        $product->setBrandCode(trim($productData->Brand));
                        $product->setBrandDesc(trim($productData->BrandDesc));
                        $product->setPackSizeCode(trim($productData->PackSize));
                        $product->setPackSizeDesc(trim($productData->PackSizeDesc));
                        $product->setCasePackCode(trim($productData->CasePack));
                        $product->setDosageCode(trim($productData->Dosage));
                        $product->setDosageDesc(trim($productData->DosageDesc));
                        $product->setStrengthCode(trim($productData->Strength));
                        $product->setStrengthDesc(trim($productData->StrengthDesc));
                        $product->setScCode(trim($productData->StorageCondition));
                        $product->setScDesc(trim($productData->StorageConditionDesc));
                        $product->setStatusSap(trim($productData->Status));
                        $product->setSapCreationDate(trim($productData->CreationDate));
                        $product->setSapChangeDate(trim($productData->ChangeDate));

                        $product->save();

                        $returnData[] = array(
                            "material_code"=>$productData->MaterialCode,
                            "status"=>1,
                            "error_code"=>"Article Information Updated.",
                        );
                    } catch (\Exception $e){
                        $returnData[] = array(
                            "material_code"=>$productData->MaterialCode,
                            "status"=>0,
                            "error_code"=>$e->getMessage(),
                        );
                    }
                } else {

                    $product = $this->_productFactory->create();

                    $product->setSku(trim($this->formatSKU($productData->EANUPC)));
                    $product->setName(trim($productData->MaterialDescription));
                    $product->setMaterial(trim($productData->MaterialCode));
                    $product->setMaterialDesc(trim($productData->MaterialDescription));
                    $product->setEanUpc(trim($productData->EANUPC));
                    $product->setDivisionCode(trim($productData->Division));
                    $product->setDivisionDesc(trim($productData->DivisonDescription));
                    $product->setProductHierarchy(trim($productData->ProductHierarchy));
                    $product->setTheraphyCode(trim($productData->Theraphy));
                    $product->setTheraphyDesc(trim($productData->TheraphyDesc));
                    $product->setSubTheraphyCode(trim($productData->SubTheraphy));
                    $product->setSubTheraphyDesc(trim($productData->SubTheraphyDesc));
                    $product->setMolecule(trim($productData->Molecule));
                    $product->setMoleculeDesc(trim($productData->MoleculeDesc));
                    $product->setBrandCode(trim($productData->Brand));
                    $product->setBrandDesc(trim($productData->BrandDesc));
                    $product->setPackSizeCode(trim($productData->PackSize));
                    $product->setPackSizeDesc(trim($productData->PackSizeDesc));
                    $product->setCasePackCode(trim($productData->CasePack));
                    $product->setDosageCode(trim($productData->Dosage));
                    $product->setDosageDesc(trim($productData->DosageDesc));
                    $product->setStrengthCode(trim($productData->Strength));
                    $product->setStrengthDesc(trim($productData->StrengthDesc));
                    $product->setScCode(trim($productData->StorageCondition));
                    $product->setScDesc(trim($productData->StorageConditionDesc));
                    $product->setStatusSap(trim($productData->Status));
                    $product->setSapCreationDate(trim($productData->CreationDate));
                    $product->setSapChangeDate(trim($productData->ChangeDate));

                    $product->setWebsiteIds(array(1));
                    $product->setAttributeSetId(4); // Attribute set id
                    $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED); // Status on product enabled/ disabled 1/0
                    $product->setWeight(0); // weight of product
                    $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
                    $product->setTaxClassId(0); // Tax class id
                    $product->setTypeId('simple'); // type of product (simple/virtual/downloadable/configurable)
                    $product->setPrice(0); // price of product
                    $product->setStockData(
                        array(
                            'use_config_manage_stock' => 0,
                            'manage_stock' => 1,
                            'is_in_stock' => 0,
                            'qty' => 0
                        )
                    );

                    try {
                        $product->save();

                        $returnData[] = array(
                            "article_code"=>$productData->MaterialCode,
                            "status"=>1,
                            "error_code"=> "New Article Created.",
                        );

                    } catch (\Exception $e) {
                        $returnData[] = array(
                            "article_code"=>$productData->MaterialCode,
                            "status"=>0,
                            "error_code"=>$e->getMessage(),
                        );
                    }

                }

            }
        }

        $this->_loggerFactory->createLog('ProductMasterResponse: '.json_encode($returnData));

        return $returnData;

	}

    private function formatSKU($sku){

        $sku = substr($sku, 0, 5).'-'.substr($sku, 5, 3).'-'.substr($sku, 8);

        return $sku;
    }
}
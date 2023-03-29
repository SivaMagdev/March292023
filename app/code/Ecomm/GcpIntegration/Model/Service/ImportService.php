<?php

/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_GcpIntegration
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Ecomm\GcpIntegration\Model\Service;

use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\File\Csv;
use Ecomm\GcpIntegration\Model\Config;
use Ecomm\GcpIntegration\Model\GcsClient;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as AttributeCollection;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Ecomm\PriceEngine\Model\ExclusivePriceFactory;
use Ecomm\PriceEngine\Model\GpoContractPriceFactory;
use Ecomm\ExclusivePrice\Model\ResourceModel\ContractPrice\CollectionFactory as ContractCollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Catalog\Model\ProductRepository;

/**
 * class import documents via csv file
 *
 */
class ImportService
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Csv
     */
    private $csv;

    /**
     * @var GcsClient
     */
    private $gcsClient;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var AttributeCollection
     */
    protected $attributeCollection;

    /**
     * @var GroupCollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ExclusivePriceFactory
     */
    protected $exclusivePriceFactory;

    /**
     * @var GpoContractPriceFactory
     */
    protected $gpoContractPriceFactory;

    /**
     * @var ContractCollectionFactory
     */
    protected $contractCollectionFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var TimezoneInterface
     */
    protected $timeZone;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var array
     */
    private $hashesExclusive = [];

    /**
     * @var array
     */
    private $hashesExclusiveIds = [];

    /**
     * @var array
     */
    private $hashesGpoContract = [];

    /**
     * @var array
     */
    private $hashesGpoContractIds = [];

    /**
     * @var array
     */
    private $hashes = [];

    /**
     * @var string
     */
    private string $delimiter = ',';

    /**
     * @var int
     */
    private $storeId = 0;

    /**
     * Customer groups by code.
     *
     * @var array
     */
    private $customerGroupsByCode = [];

    /**
     * Customer groups by code.
     *
     * @ProductRepository
     */
    private $productRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param Csv $csv
     * @param GcsClient $gcsClient
     * @param ProductFactory $productFactory
     * @param AttributeCollection $attributeCollection
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ExclusivePriceFactory $exclusivePriceFactory
     * @param GpoContractPriceFactory $gpoContractPriceFactory
     * @param ContractCollectionFactory $contractCollectionFactory
     * @param DateTime $dateTime
     * @param TimezoneInterface $timeZone
     * @param LoggerInterface $logger
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Config $config,
        Csv $csv,
        GcsClient $gcsClient,
        ProductFactory $productFactory,
        AttributeCollection $attributeCollection,
        GroupCollectionFactory $groupCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ExclusivePriceFactory $exclusivePriceFactory,
        GpoContractPriceFactory $gpoContractPriceFactory,
        ContractCollectionFactory $contractCollectionFactory,
        DateTime $dateTime,
        TimezoneInterface $timeZone,
        LoggerInterface $logger,
        ProductRepository $productRepository,
        \Ecomm\Globaldeclaration\Helper\Globalhelper $globalhelper
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->csv = $csv;
        $this->gcsClient = $gcsClient;
        $this->productFactory = $productFactory;
        $this->attributeCollection = $attributeCollection;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->exclusivePriceFactory = $exclusivePriceFactory;
        $this->gpoContractPriceFactory = $gpoContractPriceFactory;
        $this->contractCollectionFactory = $contractCollectionFactory;
        $this->dateTime = $dateTime;
        $this->timeZone = $timeZone;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->globalhelper = $globalhelper;
    }

    /**
     * For process CSV File
     *
     * @return array|\Magento\Framework\Phrase
     * @throws Exception
     */
    public function processImport()
    {
        $result = [];
        $existingProductLists = $this->getExistProductlists();
        $materialCodes = $existingProductLists['materialCodes'];
        $productIds = $existingProductLists['productIds'];
        $productSkus = $existingProductLists['productSku'];
        $subWacId = $this->getSubWacId();
        //$subWacId = '3000000438'; // Sub-WAC
        $subPhsId = $this->getPhsId();
        //$subPhsId = '3000000479'; // 340B - PHS Indirect
        $gpoContractIds = $this->getGpoIds();
        /*$gpoContractIds = [
            '3000000351', '3000000352', '3000001541', '3000001343', '3000000911', '3000000912', '3000000440', '3000000441'
        ];*/
        $rcaContractIds = $this->getRcaIds();
        $currentDate = $this->timeZone->date($this->dateTime->date('Y-m-d H:i:s'))->format('Y-m-d');
        $location = sprintf($this->config->getGcsLocation(), $currentDate);
        $importRows = $this->gcsClient->readFile($location);
        if (!$importRows) {
            $result['error'] = 'File/Data not available.';
            return $result['error'];
        }
        $header = array_shift($importRows);

        $this->setExclusivePriceHashes();
        $this->setGpoContractPriceHashes();
        $this->setProductHashes();
        $inc = 1;
        $productPrices = [];
        $productGpoPrices = [];
        $productExclusivePrices = [];
        foreach ($importRows as $rowIndex => $dataRow) {
            $rowData = array_combine($header, $dataRow);
            if(in_array((int)$rowData['Phoenix_Material_Number__c'], $materialCodes)) {
                $currentDate = $this->timeZone->date($this->dateTime->date('Y-m-d H:i:s'))->format('Y-m-d');
                $priceEffectiveFrom = $rowData['Phoenix_Price_Effective_Value_From__c'];
                $priceEffectiveTo = $rowData['Phoenix_Price_Effective_Value_To__c'];

                $currentDate = strtotime($currentDate);
                $priceEffectiveFrom = strtotime($priceEffectiveFrom);
                $priceEffectiveTo = strtotime($priceEffectiveTo);

                if($priceEffectiveFrom <= $currentDate && $priceEffectiveTo >= $currentDate) {
                    if ($rowData['Phoenix_Contract_Type__c'] == 'Indirect') {
                        $hash = $this->calculateHash($rowData);
                        //if(!in_array($hash , $this->hashes)) {
                        $productPrices[(int)$rowData['Phoenix_Material_Number__c']]['hash'] = $hash;
                        $productPrices[(int)$rowData['Phoenix_Material_Number__c']]['price'] = $rowData['Phoenix_WAC__c'];
                        if ($subWacId == $rowData['Phoenix_Contract_Number__c']) {
                            $productPrices[(int)$rowData['Phoenix_Material_Number__c']]['subWacPrice'] = $rowData['Phoenix_Contract_Price__c'];
                        } else if ($subPhsId == $rowData['Phoenix_Contract_Number__c']) {
                            $productPrices[(int)$rowData['Phoenix_Material_Number__c']]['phsPrice'] = $rowData['Phoenix_Contract_Price__c'];
                        } else {
                            if(in_array((int)$rowData['Phoenix_Contract_Number__c'], $gpoContractIds) && !in_array($hash , $this->hashesGpoContract)) {
                                $productGpoPrices[] = [
                                    'contract_id' => $rowData['Phoenix_Contract_Number__c'],
                                    'material' => (int)$rowData['Phoenix_Material_Number__c'],
                                    'sku' => $productSkus[(int)$rowData['Phoenix_Material_Number__c']],
                                    'price' => $rowData['Phoenix_Contract_Price__c'],
                                    'start_date' => $rowData['Phoenix_Price_Effective_Value_From__c'],
                                    'end_date' => $rowData['Phoenix_Price_Effective_Value_To__c'],
                                    'import_hash' => $hash
                                ];
                            }
                        }
                        //}
                    } else if ($rowData['Phoenix_Contract_Type__c'] == 'Direct') {
                        $hash = $this->calculateHash($rowData);
                        if(in_array((int)$rowData['Phoenix_Contract_Number__c'], $rcaContractIds)) {
                            $productGpoPrices[] = [
                                    'contract_id' => $rowData['Phoenix_Contract_Number__c'],
                                    'material' => (int)$rowData['Phoenix_Material_Number__c'],
                                    'sku' => $productSkus[(int)$rowData['Phoenix_Material_Number__c']],
                                    'price' => $rowData['Phoenix_Contract_Price__c'],
                                    'start_date' => $rowData['Phoenix_Price_Effective_Value_From__c'],
                                    'end_date' => $rowData['Phoenix_Price_Effective_Value_To__c'],
                                    'import_hash' => $hash
                                ];

                        } else {
                            if(!in_array($hash , $this->hashesExclusive)) {
                                $productExclusivePrices[] = [
                                    'product_sku' => (int)$rowData['Phoenix_Material_Number__c'],
                                    'ndc' => $productSkus[(int)$rowData['Phoenix_Material_Number__c']],
                                    'customer_id' => (int)$rowData['Phoenix_Customer_Number__c'],
                                    'price' => $rowData['Phoenix_Contract_Price__c'],
                                    'start_date' => $rowData['Phoenix_Price_Effective_Value_From__c'],
                                    'end_date' => $rowData['Phoenix_Price_Effective_Value_To__c'],
                                    'contract_ref' => $rowData['Phoenix_Contract_Number__c'],
                                    'import_hash' => $hash,
                                    'deleted' => 0
                                ];
                            }
                        }
                    }
                    $inc++;
                }
            }
        }

        if ($productPrices) {
            foreach ($productPrices as $materialCode => $productPrice) {
                $productId = $productIds[$materialCode];
                $product = $this->productFactory->create()->load($productId);

                if (isset($productPrice['price'])) {
                    $product->setPrice($productPrice['price']);
                    $this->globalhelper->customerRegularPriceNotification($product, "Price", $productPrice['price']);
                }
                if (isset($productPrice['phsPrice'])) {
                    $product->setPhsIndirect($productPrice['phsPrice']);
                    $this->globalhelper->customerRegularPriceNotification($product, "340b(Phs Indirect Price)", $productPrice['price']);
                }

                if (isset($productPrice['subWacPrice'])) {
                    $product->setSubWac($productPrice['subWacPrice']);
                    $this->globalhelper->customerRegularPriceNotification($product, "340b(Sub-WAC Price)", $productPrice['price']);
                }
                $product->setImportHash($productPrice['hash']);

                try {
                    $product->save();
                    $this->productRepository->save($product);
                }  catch (\Exception $e) {
                   $this->logger->notice($e->getMessage());
                }
            }
        }
        if ($productGpoPrices) {
            foreach ($productGpoPrices as $groupPrice) {
                //$gpoContractId = $this->getGpoContractPriceRowId($groupPrice['contract_id'], $groupPrice['material']);
                $data = [$groupPrice['contract_id'], $groupPrice['material']];
                $entityIdHash = $this->calculateHash($data);
                $gpoContractId = 0;
                if (in_array($entityIdHash, $this->hashesGpoContractIds)){
                    $gpoContractId = array_search($entityIdHash,$this->hashesGpoContractIds);
                }
                $gpoContractPriceModel = $this->gpoContractPriceFactory->create();
                if($gpoContractId) {
                    $entityId['entity_id'] = $gpoContractId;
                    $groupPrice = array_merge($groupPrice,$entityId);
                }
                $gpoContractPriceModel->setData($groupPrice);
                $gpoContractPriceModel->save();
            }
        }

        if ($productExclusivePrices) {
            foreach ($productExclusivePrices as $productExclusivePrice) {
                $data = [$productExclusivePrice['customer_id'], $productExclusivePrice['product_sku']];
                $entityIdHash = $this->calculateHash($data);
                $gpoExclusiveId = 0;
                if (in_array($entityIdHash, $this->hashesExclusiveIds)){
                    $gpoExclusiveId = array_search($entityIdHash,$this->hashesExclusiveIds);
                }
                $exclusivepriceModel = $this->exclusivePriceFactory->create();
                if($gpoExclusiveId) {
                    $entityId['exclusive_price_id'] = $gpoExclusiveId;
                    $productExclusivePrice = array_merge($productExclusivePrice,$entityId);
                }
                $exclusivepriceModel->setData($productExclusivePrice);
                $exclusivepriceModel->save();
            }
        }

        return __('Total Records: %1,  Product Price: %2, GPO Contratc Price:%3, Exclusive Price:%4', $inc, count($productPrices), count($productGpoPrices), count($productExclusivePrices));
    }

    /**
     * Build sql query for values
     *
     * @param SourceItemInterface[] $sourceItems
     * @return string
     */
    private function buildValuesSqlPart(array $sourceItems): string
    {
        $sql = rtrim(str_repeat('(?, ?, ?, ?), ', count($sourceItems)), ', ');
        return $sql;
    }

    /**
     * Get Sql bind data
     *
     * @param  $sourceItems
     * @return array
     */
    private function getSqlBindData(array $sourceItems): array
    {
        $bind = [];
        foreach ($sourceItems as $sourceItem) {
            $bind[] = $sourceItem['source_code'];
            $bind[] = $sourceItem['sku'];
            $bind[] = $sourceItem['quantity'];
            $bind[] = $sourceItem['status'];
        }
        return $bind;
    }

    /**
     * To set delimiter
     *
     * @param string $delimiter
     * @return void
     */
    public function setDelimiter(string $delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * To set website id
     *
     * @param int $websiteId
     * @return void
     */
    public function setWebsiteId($websiteId)
    {
        $this->websiteId = (int)$websiteId;
    }

    /**
     * To set store id
     *
     * @param int $storeId
     */
    public function setStoreId($storeId)
    {
        $this->storeId = (int)$storeId;
    }

    /**
     * set product urls
     *
     * @return array
     */
    public function getExistProductlists()
    {
        $productLists = [];
        $this->attributeCollection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->join(
                ['eav_values' => 'catalog_product_entity_varchar'],
                'main_table.attribute_id = eav_values.attribute_id',
                ['material' => 'value']
            )
            ->join(
                ['product' => 'catalog_product_entity'],
                'product.row_id = eav_values.row_id',
                ['sku' => 'sku', 'row_id' => 'product.row_id', 'entity_id' => 'product.entity_id']
            )
            ->where('main_table.entity_type_id = ?', 4)
            ->where('main_table.attribute_code = ?', 'material')
            ->where('eav_values.store_id = ?', 0);
        $productCodes = $this->attributeCollection->getItems();
        foreach ($productCodes as $p) {
            $productLists['materialCodes'][] = $p->getData('material');
            $productLists['productIds'][$p->getData('material')] = $p->getData('entity_id');
            $productLists['productSku'][$p->getData('material')] = $p->getData('sku');

            $product = $this->productFactory->create()->load($p->getData('entity_id'));
            $product->setPrice(0);
            $product->setPhsIndirect(0);
            $product->setSubWac(0);
            $product->save();
        }
        return $productLists;
    }

    /**
     * Retrieve customer group id by sap customer group id.
     *
     * @param string $sapCustomerGroupId
     * @param string $groupName
     * @return int
     */
    private function retrieveGroupValue($gpoContractId)
    {
        try {
            if (!isset($this->customerGroupsByCode[$gpoContractId])) {
                $groupCollection = $this->groupCollectionFactory->create();
                $groupCollection->addFieldToFilter('gpo_contract_id', $gpoContractId);
                $groupCollection->addFieldToSelect(
                    ['gpo_contract_id', 'customer_group_id', 'customer_group_code']
                );


                $group = $groupCollection->getFirstItem();
                $this->customerGroupsByCode[strtolower($group->getGpoContractId())] = $group->getId();

            }
            if (isset($this->customerGroupsByCode[strtolower($gpoContractId)])) {
                return $this->customerGroupsByCode[strtolower($gpoContractId)];
            } else {
                return 0;
            }
        } catch (\Exception $ex) {
            $this->logger->warning($ex->getMessage());
        }
    }

    /**
     * Retrieve customer group with contract id
     *
     * @return array
     */
    private function getGroupContractIds()
    {
        $customerGroups = [];
        try {
            $groupCollection = $this->groupCollectionFactory->create();
            foreach ($groupCollection->getItems() as $customerGroup) {
                //echo '<pre>'.print_r($customerGroup->getData(), true);
                $customerGroups[$customerGroup->getCustomerGroupId()] = $customerGroup->getGpoContractId();
            }
            return $customerGroups;
        } catch (\Exception $ex) {
            $this->logger->warning($ex->getMessage());
        }
    }

    /**
     * @param array $rowData
     *
     * @return false|string
     */
    public function calculateHash(array $rowData)
    {
        return hash("crc32b", implode('', $rowData));
    }

    /**
     * get Exclusive price Hashes
     *
     */
    private function setExclusivePriceHashes()
    {
        $hashes = [];
        $hashesBYFilter = [];
        /*$exclusivePricecollections = $this->exclusivePriceFactory->create()->getCollection()->addFieldToSelect(['exclusive_price_id', 'import_hash', 'customer_id', 'product_sku']);

        foreach ($exclusivePricecollections as $exclusivePrice) {
            $hashes[$exclusivePrice['exclusive_price_id']] = $exclusivePrice->getData('import_hash');
            $data = [$exclusivePrice->getData('customer_id'), $exclusivePrice->getData('product_sku')];
            $hashesBYFilter[$exclusivePrice->getData('exclusive_price_id')] = $this->calculateHash($data);
        }*/
        $connection = $this->exclusivePriceFactory->create()->getCollection()->getConnection();
        $tableName = $this->exclusivePriceFactory->create()->getCollection()->getMainTable();
        $connection->truncateTable($tableName);
        $this->hashesExclusive = $hashes;
        $this->hashesExclusiveIds = $hashesBYFilter;
    }

    /**
     * get Exclusive price Hashes
     *
     */
    private function setGpoContractPriceHashes()
    {
        $hashes = [];
        $hashesBYFilter = [];
        /*$exclusivePricecollections = $this->gpoContractPriceFactory->create()->getCollection()->addFieldToSelect(['entity_id', 'import_hash', 'contract_id', 'material']);

        foreach ($exclusivePricecollections as $exclusivePrice) {
            $hashes[$exclusivePrice->getData('entity_id')] = $exclusivePrice->getData('import_hash');
            $data = [$exclusivePrice->getData('contract_id'), $exclusivePrice->getData('material')];
            $hashesBYFilter[$exclusivePrice->getData('entity_id')] = $this->calculateHash($data);
        }*/
        $connection = $this->gpoContractPriceFactory->create()->getCollection()->getConnection();
        $tableName = $this->gpoContractPriceFactory->create()->getCollection()->getMainTable();
        $connection->truncateTable($tableName);
        $this->hashesGpoContract = $hashes;
        $this->hashesGpoContractIds = $hashesBYFilter;
    }

    /**
     * get Gpo contract price row id
     *
     */
    private function getGpoContractPriceRowId($contractId, $material)
    {
        $exclusivePricecollections = $this->gpoContractPriceFactory->create()->getCollection()->addFieldToSelect(['entity_id'])->addFieldToFilter('contract_id', $contractId)->addFieldToFilter('material', $material)->getFirstItem();
        //print_r($exclusivePricecollections->getData());
        return $exclusivePricecollections->getData('entity_id');
    }

    /**
     * get product Hashes
     *
     */
    private function setProductHashes()
    {
        $hashes = [];
        $products = $this->productCollectionFactory->create();
        $products->addAttributeToSelect('sku, import_hash');

        foreach ($products as $product) {
            $hashes[$product->getData('sku')] = $product->getData('import_hash');
        }
        $this->hashes = $hashes;
    }

    /**
     * get Sub Wac ID
     *
     */
    private function getSubWacId() {
        $collection = $this->contractCollectionFactory->create();
        $collection->addFieldToFilter('contract_type', ['eq' => 'Sub Wac'])
           ->addFieldToFilter('deleted', ['neq' => 1])
           ->load();
        if($collection->getSize()){
            $data = $collection->getFirstItem();
            return $data->getContractId();
        }

        return null;
    }

    /**
     * get PHS ID
     *
     */
    private function getPhsId() {
        $collection = $this->contractCollectionFactory->create();
        $collection->addFieldToFilter('contract_type', ['eq' => 'PHS Indirect'])
           ->addFieldToFilter('deleted', ['neq' => 1])
           ->load();
        if($collection->getSize()){
            $data = $collection->getFirstItem();
            return $data->getContractId();
        }

        return null;
    }

    /**
     * get GPO IDs
     *
     */
    private function getGpoIds() {
        $gpoIds = [];
        $collections = $this->contractCollectionFactory->create();
        $collections->addFieldToFilter('contract_type', ['eq' => 'GPO'])
           ->addFieldToFilter('deleted', ['neq' => 1])
           ->load();
        if($collections->getSize()){
            foreach($collections as $collection) {
                $gpoIds[] = $collection->getContractId();

            }
            return $gpoIds;
        }

        return [];
    }

    /**
     * get GPO IDs
     *
     */
    private function getRcaIds() {
        $rcaIds = [];
        $collections = $this->contractCollectionFactory->create();
        $collections->addFieldToFilter('contract_type', ['eq' => 'RCA'])
           ->addFieldToFilter('deleted', ['neq' => 1])
           ->load();
        if($collections->getSize()){
            foreach($collections as $collection) {
                $rcaIds[] = $collection->getContractId();

            }
            return $rcaIds;
        }

        return [];
    }
}

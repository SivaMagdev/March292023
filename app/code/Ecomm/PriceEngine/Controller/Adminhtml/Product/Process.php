<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Product;

use Magento\Framework\App\Filesystem\DirectoryList;
//use Ecomm\PriceEngine\Model\ProductFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

/**
 * Process controller class
 */
class Process extends \Magento\Backend\App\Action
{
    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_support/email';

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/name';

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $helper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $directoryList;

    protected $storeManager;

    protected $_productFactory;

    protected $_productRepository;

    protected $_attributeFactory;

    protected $_eavAttribute;

    protected $_eavConfig;

    protected $indexFactory;

    protected $indexCollection;

    /**
     * Constructor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomm\Notification\Helper\Data $helper,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\File\Csv $csv,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Indexer\Model\IndexerFactory $indexFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexCollection,
        TypeListInterface $typeListInterface,
        Pool $pool,
        \Magento\Catalog\Model\Product $productModel
    ) {
        $this->_transportBuilder        = $transportBuilder;
        $this->inlineTranslation        = $inlineTranslation;
        $this->scopeConfig              = $scopeConfig;
        $this->helper                   = $helper;
        $this->resultPageFactory        = $resultPageFactory;
        $this->directoryList            = $directoryList;
        $this->storeManager             = $storeManager;
        $this->_productFactory          = $productFactory;
        $this->_productRepository       = $productRepository;
        $this->_attributeFactory        = $attributeFactory;
        $this->_eavAttribute            = $eavAttribute;
        $this->csv                      = $csv;
        $this->_eavConfig               = $eavConfig;
        $this->indexFactory              = $indexFactory;
        $this->indexCollection           = $indexCollection;
        $this->typeListInterface         = $typeListInterface;
        $this->pool                      = $pool;
        $this->_productModel             = $productModel;   
        parent::__construct($context);
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecomm_PriceEngine::product_import');
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $isPost = $this->getRequest()->isPost();
        try {
            if ($isPost) {

                $this->inlineTranslation->suspend();

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $sender = [
                    'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope),
                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                ];

                $templateVars = [];

                $file = $_FILES['import_csv_file'];
                $allowed = array('csv');
                $filename = $_FILES['import_csv_file']['name'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!in_array($ext, $allowed)) {
                    $this->messageManager->addError("File type is wrong, please upload file in csv format.");
                    //exit();
                } else {
                    $data = $this->getRequest()->getParam('import_csv_file');
                    $this->_getSession()->setFormData($data);

                    $csvProcessor = $this->csv;
                    $importProductRawData = $csvProcessor->getData($file['tmp_name']);

                    $mandatory_columns = [];
                    $headers = [];

                    $mandatory_error = '';


                    $products_array = [];
                    $dosage_form_array = [];
                    $container_type_array = [];
                    $theraputic_cat_array = [];
                    $brand_name_array = [];
                    $strength_array = [];
                    $pack_size_array = [];
                    $molecule_array = [];

                    $counter=1;

                    foreach ($importProductRawData as $rowIndex => $dataRow) {

                        //echo '<pre>'.print_r($dataRow, true).'</pre>'; exit();

                        if($counter == 1){
                            $headers = $dataRow;
                        } else if($counter == 2){
                            $mandatory_columns = $dataRow;
                        }

                        if ($counter > 2) {

                            foreach($dataRow as $idx=>$rowInfo){
                                if($mandatory_columns[$idx] != '' && $rowInfo == ''){
                                    //echo $idx.' - '.$account_group.' - '.$rowInfo.'<br />';
                                    $mandatory_error .= 'On Row '.$counter.' coloumn '.$headers[$idx].' is missing!<br />';

                                }
                            }

                            $products_array[] = $dataRow;

                            if(trim($dataRow[4]) != ''){
                                $dosage_form_array[] = trim($dataRow[4]);
                            }
                            if(trim($dataRow[5]) != ''){
                                $container_type_array[] = trim($dataRow[5]); // Container Type - drl_division
                            }
                            if(trim($dataRow[6]) != ''){
                                $theraputic_cat_array[] = trim($dataRow[6]);
                            }
                            if(trim($dataRow[9]) != ''){
                                $brand_name_array[] = trim($dataRow[9]);
                            }
                            if(trim($dataRow[12]) != ''){
                                $strength_array[] = trim($dataRow[12]);
                            }
                            if(trim($dataRow[13]) != ''){
                                $pack_size_array[] = trim($dataRow[13]);
                            }
                            if(trim($dataRow[34]) != ''){
                                $molecule_array[] = trim($dataRow[34]);
                            }

                        }

                        $counter++;
                    }

                    if($mandatory_error == '') {

                        $configurable_skus = array_unique( array_diff_assoc( $molecule_array, array_unique( $molecule_array ) ) );

                        //echo '<pre>configurable_skus: '.print_r($configurable_skus, true).'</pre>';
                        //exit();

                        $dosage_form_array = array_unique($dosage_form_array);
                        $theraputic_cat_array = array_unique($theraputic_cat_array);
                        $container_type_array = array_unique($container_type_array);
                        $brand_name_array = array_unique($brand_name_array);
                        $strength_array = array_unique($strength_array);
                        $pack_size_array = array_unique($pack_size_array);



                        //echo '<pre>dosage_form_array: '.print_r($dosage_form_array, true).'</pre>';
                        //echo '<pre>theraputic_cat_array: '.print_r($theraputic_cat_array, true).'</pre>';
                        //echo '<pre>brand_name_array: '.print_r($brand_name_array, true).'</pre>';
                        //echo '<pre>strength_array: '.print_r($strength_array, true).'</pre>';
                        //echo '<pre>pack_size_array: '.print_r($pack_size_array, true).'</pre>';
                        //echo '<pre>container_type_array: '.print_r($container_type_array, true).'</pre>';

                        //exit();

                        $this->setAttributeValue('dosage_form', $dosage_form_array);
                        $this->setAttributeValue('theraputic_cat', $theraputic_cat_array);
                        $this->setAttributeValue('drl_division', $container_type_array);
                        $this->setAttributeValue('brand_name', $brand_name_array);
                        $this->setAttributeValue('strength', $strength_array);
                        $this->setAttributeValue('pack_size', $pack_size_array);
                        //exit();

                        $dosage_form_list = $this->getAttributeValues('dosage_form');
                        $theraputic_cat_list = $this->getAttributeValues('theraputic_cat');
                        $container_type_list = $this->getAttributeValues('drl_division');
                        $brand_name_list = $this->getAttributeValues('brand_name');
                        $strength_list = $this->getAttributeValues('strength');
                        $pack_size_list = $this->getAttributeValues('pack_size');
                        $drd_product_type_list = $this->getAttributeValues('drd_product_type');

                        $latex_free_list = $this->getAttributeValues('latex_free');
                        $preservative_free_list = $this->getAttributeValues('preservative_free');
                        $gluten_free_list = $this->getAttributeValues('gluten_free');
                        $dye_free_list = $this->getAttributeValues('dye_free');

                        //echo '<pre>dosage_form_list: '.print_r($dosage_form_list, true).'</pre>';
                        //echo '<pre>dosage_form_list: '.print_r($theraputic_cat_list, true).'</pre>';
                        //echo '<pre>Container Type: '.print_r($container_type_list, true).'</pre>';
                        //echo '<pre>dosage_form_list: '.print_r($brand_name_list, true).'</pre>';
                        //echo '<pre>dosage_form_list: '.print_r($strength_list, true).'</pre>';
                        //echo '<pre>dosage_form_list: '.print_r($pack_size_list, true).'</pre>';
                        //echo '<pre>latex_free_list: '.print_r($latex_free_list, true).'</pre>';
                        //echo '<pre>preservative_free_list: '.print_r($preservative_free_list, true).'</pre>';

                        //$c_return = $this->createConfigurableProduct($configurable_skus, $products_array);

                        //exit();

                        $associatedproducts = [];

                        $counter=1;

                        $import = 0;

                        $not_exists = '';

                        foreach ($products_array as $rowIndex => $dataRow) {

                            //echo '<pre>'.print_r($dataRow, true).'</pre>'; exit();
                            $sku = trim($dataRow[0]);

                            if($sku) {
                                try {

                                    //echo trim($dataRow[0]).' - '.trim($dataRow[1]).' - '.trim($dataRow[31]).'<br />'; exit();
                                    //$_product = $this->_productRepository->get($sku);
                                    $product = $this->_productFactory->create()->loadByAttribute('material', $dataRow[0]);

                                    //echo '<pre>'.print_r($_product, true).'</pre>'; exit();

                                    //echo $_product->getId().'<br />';
                                    if($product) {
                                        //$product = $this->_productFacory->create()->load($_product->getId());

                                        //echo trim($dataRow[8]).'<br />';

                                        if(in_array(trim($dataRow[34]), $configurable_skus)){
                                            //$associatedproducts[$dataRow[34]][] = $product->getId();
                                            $associatedproducts[$dataRow[34]][] = $dataRow[1];
                                            //$product->setVisibility(3); // visibilty of product (catalog / search / catalog, search / Not visible individually)
                                            $product->setVisibility(4);
                                        } else {
                                            $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
                                        }

                                        //echo $product->getId().' - '.trim($dataRow[29]).'<br />';

                                        //$product->setSku(trim($dataRow[0]));
                                        /*$websiteId = $this->storeManager->getWebsite()->getWebsiteId();
                                        $store = $this->storeManager->getStore();
                                        $storeId = $store->getStoreId();  // Get Store ID*/
                                        //$product->setWebsiteIds(array(1));
                                        $product->setNdc(str_replace('-','',trim($dataRow[1])));
                                        $product->setBarcode($this->barCodeFormate(str_replace('-','',trim($dataRow[1]))));
                                        $product->setMaterial(trim($dataRow[0]));
                                        $product->setName(trim($dataRow[2]));
                                        $product->setUrlKey(trim($dataRow[2]).'-'.trim($dataRow[1]));
                                        $product->setDeaClass(trim($dataRow[3]));
                                        $product->setDosageForm(array_search(trim($dataRow[4]),$dosage_form_list));
                                        $product->setDrlDivision(array_search(trim($dataRow[5]),$container_type_list));
                                        //$product->setDrlDivision(trim($dataRow[5]));
                                        $product->setTheraputicCat(array_search(trim($dataRow[6]),$theraputic_cat_list));
                                        $product->setMetaKeyword(trim($dataRow[7]));
                                        $product->setFdaRating(trim($dataRow[8]));
                                        $product->setBrandName(array_search(trim($dataRow[9]),$brand_name_list));
                                        $product->setSpecialHandlingStorage(trim($dataRow[10]));
                                        $cold_chain = 0;
                                        if(strtolower(trim($dataRow[11])) == 'yes') { $cold_chain = 1; }
                                        $product->setColdChain($cold_chain);

                                        $product->setStrength(array_search(trim($dataRow[12]),$strength_list));
                                        $product->setPackSize(array_search(trim($dataRow[13]),$pack_size_list));

                                        $product->setShortDescription(trim($dataRow[14]));
                                        $product->setCasePack(trim($dataRow[15]));
                                        $product->setClosure(trim($dataRow[16]));
                                        $product->setVialSize(trim($dataRow[17]));
                                        $product->setConcentration(trim($dataRow[18]));
                                        $latex_free = '';
                                        $preservative_free = '';
                                        $gluten_free = '';
                                        $dye_free = '';
                                        $bar_coded = 0;
                                        $black_box = 0;

                                        if(strtolower(trim($dataRow[19])) == 'yes') {
                                            $latex_free = array_search('Yes',$latex_free_list);
                                        } else if(strtolower(trim($dataRow[19])) == 'no') {
                                            $latex_free = array_search('No',$latex_free_list);
                                        }

                                        if(strtolower(trim($dataRow[20])) == 'yes') {
                                            $preservative_free = array_search('Yes',$preservative_free_list);
                                        } else if(strtolower(trim($dataRow[20])) == 'no') {
                                            $preservative_free = array_search('No',$preservative_free_list);
                                        }

                                        if(strtolower(trim($dataRow[21])) == 'yes') {
                                            $gluten_free = array_search('Yes',$gluten_free_list);
                                        } else if(strtolower(trim($dataRow[21])) == 'no') {
                                            $gluten_free = array_search('No',$gluten_free_list);
                                        }

                                        if(strtolower(trim($dataRow[22])) == 'yes') {
                                            $dye_free = array_search('Yes',$dye_free_list);
                                        } else if(strtolower(trim($dataRow[22])) == 'no') {
                                            $dye_free = array_search('No',$dye_free_list);
                                        }

                                        //echo $latex_free.'-'.$preservative_free.'-'.$gluten_free.'-'.$dye_free; exit();

                                        if(strtolower(trim($dataRow[23])) == 'yes') { $bar_coded = 1; }
                                        if(strtolower(trim($dataRow[33])) == 'yes') { $black_box = 1; }
                                        $product->setLatexFree($latex_free);
                                        $product->setPreservativeFree($preservative_free);
                                        $product->setGlutenFree($gluten_free);
                                        $product->setDyeFree($dye_free);
                                        $product->setBarCoded($bar_coded);
                                        $product->setBlackBox($black_box);
                                        $product->setCapColor(trim($dataRow[24]));

                                        if(trim($dataRow[25]) != ''){
                                            $product->setLinkMedication(trim($dataRow[25]));
                                        }

                                        if(trim($dataRow[26]) != ''){
                                            $product->setLinkPrescribing(trim($dataRow[26]));
                                        }

                                        if(trim($dataRow[27]) != ''){
                                            $product->setLinkDailymed(trim($dataRow[27]));
                                        }
                                        //$product->setLinkCpsia(trim($dataRow[30]));
                                        if(trim($dataRow[28]) != ''){
                                            $product->setLinkMsds(trim($dataRow[28]));
                                        }

                                        //$product->setShortDescription(trim($dataRow[32]));
                                        //$product->setAmerisourceBergen6(trim($dataRow[34]));
                                        $product->setAmerisourceBergen(trim($dataRow[29]));
                                        $product->setCardinal(trim($dataRow[30]));
                                        //$product->setDsmith(trim($dataRow[31]));
                                        $product->setMckesson(trim($dataRow[31]));
                                        $product->setMD(trim($dataRow[32]));

                                        $product->setMolecule(trim($dataRow[34]));
                                        $product->setMoleculeDesc(trim($dataRow[35]));
                                        $product->setMaterialDesc(trim($dataRow[36]));
                                        $product->setDosageCode(trim($dataRow[37]));
                                        $product->setProductHierarchy(trim($dataRow[38]));
                                        $product->setTheraphyCode(trim($dataRow[39]));
                                        $product->setStrengthCode(trim($dataRow[40]));
                                        $product->setPackSizeCode(trim($dataRow[41]));
                                        $product->setDrdProductType(array_search(trim($dataRow[42]),$drd_product_type_list));

                                        $product->setStoreId(0);

                                        /*$product->setDivisionCode(trim($dataRow[41]));
                                        $product->setDivisionDesc(trim($dataRow[42]));
                                        $product->setTheraphyDesc(trim($dataRow[45]));
                                        $product->setSubTheraphyCode(trim($dataRow[46]));
                                        $product->setSubTheraphyDesc(trim($dataRow[47]));
                                        $product->setBrandCode(trim($dataRow[50]));
                                        $product->setBrandDesc(trim($dataRow[51]));
                                        $product->setPackSizeCode(trim($dataRow[52]));
                                        $product->setCasePackCode(trim($dataRow[53]));
                                        $product->setDosageDesc(trim($dataRow[55]));
                                        $product->setScCode(trim($dataRow[57]));
                                        $product->setScDesc(trim($dataRow[58]));
                                        $product->setStatusSap(trim($dataRow[59]));
                                        $product->setSapCreationDate(trim($dataRow[60]));*/

                                        //$product->setPrice(200); // price of product
                                        //$product->setDescription('Description test test test test test test'); // Description of product
                                        //$product->setShortDescription('Short Description test test test test test test'); // Short Description of product
                                        //$product->setStoreId($_product->getStoreId()); // price of product
                                        //$product->setWebsiteIds(array(1));
                                        //$categories = [3];
                                        //$product->setCategoryIds($categories);
                                        /*$product->setStockData(
                                            array(
                                                'use_config_manage_stock' => 0,
                                                'manage_stock' => 1,
                                                'is_in_stock' => 1,
                                                'qty' => 100
                                            )
                                        );*/

                                        //$product->save();
                                        $this->_productRepository->save($product);
                                    } else {
                                        //echo trim($dataRow[0]).' - '.trim($dataRow[1]).' - '.trim($dataRow[31]).'<br />';

                                        if($this->_productModel->getIdBySku($dataRow[1])) {
                                            $this->_redirect('*/*/import');
                                            $this->messageManager->addError(__('Some of the products are already available with the same NDC which are present in import file. Please check the NDC and try again.'));
                                            return;    
                                        }

                                        $product = $this->_productFactory->create();
                                        $product->setSku(trim($dataRow[1]));
                                        $product->setNdc(str_replace('-','',trim($dataRow[1])));
                                        $product->setBarcode($this->barCodeFormate(str_replace('-','',trim($dataRow[1]))));
                                        $product->setMaterial(trim($dataRow[0]));
                                        $product->setName(trim($dataRow[2]));
                                        $product->setUrlKey(trim($dataRow[2]).'-'.trim($dataRow[1]));
                                        $product->setDeaClass(trim($dataRow[3]));
                                        $product->setDosageForm(array_search(trim($dataRow[4]),$dosage_form_list));
                                        $product->setDrlDivision(array_search(trim($dataRow[5]),$container_type_list));
                                        //$product->setDrlDivision(trim($dataRow[5]));
                                        $product->setTheraputicCat(array_search(trim($dataRow[6]),$theraputic_cat_list));
                                        $product->setMetaKeyword(trim($dataRow[7]));
                                        $product->setFdaRating(trim($dataRow[8]));
                                        $product->setBrandName(array_search(trim($dataRow[9]),$brand_name_list));
                                        $product->setSpecialHandlingStorage(trim($dataRow[10]));
                                        $cold_chain = 0;
                                        if(strtolower(trim($dataRow[11])) == 'yes') { $cold_chain = 1; }
                                        $product->setColdChain($cold_chain);
                                        $product->setStrength(array_search(trim($dataRow[12]),$strength_list));
                                        $product->setPackSize(array_search(trim($dataRow[13]),$pack_size_list));
                                        $product->setShortDescription(trim($dataRow[14]));
                                        $product->setCasePack(trim($dataRow[15]));
                                        $product->setClosure(trim($dataRow[16]));
                                        $product->setVialSize(trim($dataRow[17]));
                                        $product->setConcentration(trim($dataRow[18]));

                                        $latex_free = '';
                                        $preservative_free = '';
                                        $gluten_free = '';
                                        $dye_free = '';
                                        $bar_coded = 0;
                                        $black_box = 0;

                                        if(strtolower(trim($dataRow[19])) == 'yes') {
                                            $latex_free = array_search('Yes',$latex_free_list);
                                        } else if(strtolower(trim($dataRow[19])) == 'no') {
                                            $latex_free = array_search('No',$latex_free_list);
                                        }

                                        if(strtolower(trim($dataRow[20])) == 'yes') {
                                            $preservative_free = array_search('Yes',$preservative_free_list);
                                        } else if(strtolower(trim($dataRow[20])) == 'no') {
                                            $preservative_free = array_search('No',$preservative_free_list);
                                        }

                                        if(strtolower(trim($dataRow[21])) == 'yes') {
                                            $gluten_free = array_search('Yes',$gluten_free_list);
                                        } else if(strtolower(trim($dataRow[21])) == 'no') {
                                            $gluten_free = array_search('No',$gluten_free_list);
                                        }

                                        if(strtolower(trim($dataRow[22])) == 'yes') {
                                            $dye_free = array_search('Yes',$dye_free_list);
                                        } else if(strtolower(trim($dataRow[22])) == 'no') {
                                            $dye_free = array_search('No',$dye_free_list);
                                        }
                                        if(strtolower(trim($dataRow[23])) == 'yes') { $bar_coded = 1; }
                                        if(strtolower(trim($dataRow[33])) == 'yes') { $black_box = 1; }
                                        $product->setLatexFree($latex_free);
                                        $product->setPreservativeFree($preservative_free);
                                        $product->setGlutenFree($gluten_free);
                                        $product->setDyeFree($dye_free);
                                        $product->setBarCoded($bar_coded);
                                        $product->setBlackBox($black_box);
                                        $product->setCapColor(trim($dataRow[24]));

                                        if(trim($dataRow[25]) != ''){
                                            $product->setLinkMedication(trim($dataRow[25]));
                                        }

                                        if(trim($dataRow[26]) != ''){
                                            $product->setLinkPrescribing(trim($dataRow[26]));
                                        }

                                        if(trim($dataRow[27]) != ''){
                                            $product->setLinkDailymed(trim($dataRow[27]));
                                        }
                                        //$product->setLinkCpsia(trim($dataRow[30]));
                                        if(trim($dataRow[28]) != ''){
                                            $product->setLinkMsds(trim($dataRow[28]));
                                        }
                                        //$product->setShortDescription(trim($dataRow[32]));
                                        //$product->setAmerisourceBergen6(trim($dataRow[34]));
                                        $product->setAmerisourceBergen(trim($dataRow[29]));
                                        $product->setCardinal(trim($dataRow[30]));
                                        //$product->setDsmith(trim($dataRow[31]));
                                        $product->setMckesson(trim($dataRow[31]));
                                        $product->setMD(trim($dataRow[32]));

                                        $product->setMolecule(trim($dataRow[34]));
                                        $product->setMoleculeDesc(trim($dataRow[35]));
                                        $product->setMaterialDesc(trim($dataRow[36]));
                                        $product->setDosageCode(trim($dataRow[37]));
                                        $product->setProductHierarchy(trim($dataRow[38]));
                                        $product->setTheraphyCode(trim($dataRow[39]));
                                        $product->setStrengthCode(trim($dataRow[40]));
                                        $product->setPackSizeCode(trim($dataRow[41]));
                                        $product->setDrdProductType(array_search(trim($dataRow[42]),$drd_product_type_list));

                                        /*$product->setDivisionCode(trim($dataRow[41]));
                                        $product->setDivisionDesc(trim($dataRow[42]));
                                        $product->setTheraphyDesc(trim($dataRow[45]));
                                        $product->setSubTheraphyCode(trim($dataRow[46]));
                                        $product->setSubTheraphyDesc(trim($dataRow[47]));
                                        $product->setBrandCode(trim($dataRow[50]));
                                        $product->setBrandDesc(trim($dataRow[51]));
                                        $product->setPackSizeCode(trim($dataRow[52]));
                                        $product->setCasePackCode(trim($dataRow[53]));
                                        $product->setDosageDesc(trim($dataRow[55]));
                                        $product->setScCode(trim($dataRow[57]));
                                        $product->setScDesc(trim($dataRow[58]));
                                        $product->setStatusSap(trim($dataRow[59]));
                                        $product->setSapCreationDate(trim($dataRow[60]));*/

                                        $product->setAttributeSetId(4); // Attribute set id
                                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED); // Status on product
                                        $product->setWeight(0); // weight of product

                                        if(in_array(trim($dataRow[34]), $configurable_skus)){
                                            $associatedproducts[$dataRow[34]][] = $dataRow[1];
                                            //$product->setVisibility(3); // visibilty of product (catalog / search / catalog, search / Not visible individually)
                                            $product->setVisibility(4);
                                        } else {
                                            $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
                                        }
                                        $product->setTaxClassId(0); // Tax class id
                                        $product->setTypeId('simple'); // type of product (simple/virtual/downloadable/configurable)
                                        $product->setPrice(0); // price of product
                                        $product->setStockData(
                                            array(
                                                'use_config_manage_stock' => 0,
                                                'manage_stock' => 1,
                                                'is_in_stock' => 1,
                                                'qty' => 0
                                            )
                                        );
                                        $product->setWebsiteIds(array(1));
                                        $categories = [4];
                                        $product->setCategoryIds($categories);
                                        try{
                                            //echo 'create new product 1'; exit();
                                            //$product->save();
                                            $this->_productRepository->save($product);
                                        } catch (\Exception $e) {
                                            //echo $e;
                                            $not_exists.='<br />' . __('SKU') . ' <b>"' . $sku . '"</b> ' . $e.' '.$counter;
                                        }
                                    }


                                } catch (\Magento\Framework\Exception\NoSuchEntityException $e){

                                    //echo trim($dataRow[0]).' - '.trim($dataRow[1]).' - '.trim($dataRow[31]).'<br />';

                                    $product = $this->_productFactory->create();
                                    $product->setSku(trim($dataRow[1]));
                                    $product->setNdc(str_replace('-','',trim($dataRow[1])));
                                    $product->setBarcode($this->barCodeFormate(str_replace('-','',trim($dataRow[1]))));
                                    $product->setMaterial(trim($dataRow[0]));
                                    $product->setName(trim($dataRow[2]));
                                    $product->setUrlKey(trim($dataRow[2]).'-'.trim($dataRow[1]));
                                    $product->setDeaClass(trim($dataRow[3]));
                                    $product->setDosageForm(array_search(trim($dataRow[4]),$dosage_form_list));
                                    $product->setDrlDivision(array_search(trim($dataRow[5]),$container_type_list));
                                    //$product->setDrlDivision(trim($dataRow[5]));
                                    $product->setTheraputicCat(array_search(trim($dataRow[6]),$theraputic_cat_list));
                                    $product->setMetaKeyword(trim($dataRow[7]));
                                    $product->setFdaRating(trim($dataRow[8]));
                                    $product->setBrandName(array_search(trim($dataRow[9]),$brand_name_list));
                                    $product->setSpecialHandlingStorage(trim($dataRow[10]));
                                    $cold_chain = 0;
                                    if(strtolower(trim($dataRow[11])) == 'yes') { $cold_chain = 1; }
                                    $product->setColdChain($cold_chain);
                                    $product->setStrength(array_search(trim($dataRow[12]),$strength_list));
                                    $product->setPackSize(array_search(trim($dataRow[13]),$pack_size_list));
                                    $product->setShortDescription(trim($dataRow[14]));
                                    $product->setCasePack(trim($dataRow[15]));
                                    $product->setClosure(trim($dataRow[16]));
                                    $product->setVialSize(trim($dataRow[17]));
                                    $product->setConcentration(trim($dataRow[18]));
                                    $latex_free = '';
                                    $preservative_free = '';
                                    $gluten_free = '';
                                    $dye_free = '';
                                    $bar_coded = 0;
                                    $black_box = 0;

                                    if(strtolower(trim($dataRow[19])) == 'yes') {
                                        $latex_free = array_search('Yes',$latex_free_list);
                                    } else if(strtolower(trim($dataRow[19])) == 'no') {
                                        $latex_free = array_search('No',$latex_free_list);
                                    }

                                    if(strtolower(trim($dataRow[20])) == 'yes') {
                                        $preservative_free = array_search('Yes',$preservative_free_list);
                                    } else if(strtolower(trim($dataRow[20])) == 'no') {
                                        $preservative_free = array_search('No',$preservative_free_list);
                                    }

                                    if(strtolower(trim($dataRow[21])) == 'yes') {
                                        $gluten_free = array_search('Yes',$gluten_free_list);
                                    } else if(strtolower(trim($dataRow[21])) == 'no') {
                                        $gluten_free = array_search('No',$gluten_free_list);
                                    }

                                    if(strtolower(trim($dataRow[22])) == 'yes') {
                                        $dye_free = array_search('Yes',$dye_free_list);
                                    } else if(strtolower(trim($dataRow[22])) == 'no') {
                                        $dye_free = array_search('No',$dye_free_list);
                                    }
                                    if(strtolower(trim($dataRow[23])) == 'yes') { $bar_coded = 1; }
                                    if(strtolower(trim($dataRow[33])) == 'yes') { $black_box = 1; }
                                    $product->setLatexFree($latex_free);
                                    $product->setPreservativeFree($preservative_free);
                                    $product->setGlutenFree($gluten_free);
                                    $product->setDyeFree($dye_free);
                                    $product->setBarCoded($bar_coded);
                                    $product->setBlackBox($black_box);
                                    $product->setCapColor(trim($dataRow[24]));

                                    if(trim($dataRow[25]) != ''){
                                        $product->setLinkMedication(trim($dataRow[25]));
                                    }

                                    if(trim($dataRow[26]) != ''){
                                        $product->setLinkPrescribing(trim($dataRow[26]));
                                    }

                                    if(trim($dataRow[27]) != ''){
                                        $product->setLinkDailymed(trim($dataRow[27]));
                                    }
                                    //$product->setLinkCpsia(trim($dataRow[30]));
                                    if(trim($dataRow[28]) != ''){
                                        $product->setLinkMsds(trim($dataRow[28]));
                                    }

                                    //$product->setShortDescription(trim($dataRow[32]));
                                    //$product->setAmerisourceBergen6(trim($dataRow[34]));
                                    $product->setAmerisourceBergen(trim($dataRow[29]));
                                    $product->setCardinal(trim($dataRow[30]));
                                    //$product->setDsmith(trim($dataRow[31]));
                                    $product->setMckesson(trim($dataRow[31]));
                                    $product->setMD(trim($dataRow[32]));

                                    $product->setMolecule(trim($dataRow[34]));
                                    $product->setMoleculeDesc(trim($dataRow[35]));
                                    $product->setMaterialDesc(trim($dataRow[36]));
                                    $product->setDosageCode(trim($dataRow[37]));
                                    $product->setProductHierarchy(trim($dataRow[38]));
                                    $product->setTheraphyCode(trim($dataRow[39]));
                                    $product->setStrengthCode(trim($dataRow[40]));
                                    $product->setPackSizeCode(trim($dataRow[41]));
                                    $product->setDrdProductType(array_search(trim($dataRow[42]),$drd_product_type_list));

                                    /*$product->setDivisionCode(trim($dataRow[41]));
                                    $product->setDivisionDesc(trim($dataRow[42]));
                                    $product->setTheraphyDesc(trim($dataRow[45]));
                                    $product->setSubTheraphyCode(trim($dataRow[46]));
                                    $product->setSubTheraphyDesc(trim($dataRow[47]));
                                    $product->setBrandCode(trim($dataRow[50]));
                                    $product->setBrandDesc(trim($dataRow[51]));
                                    $product->setPackSizeCode(trim($dataRow[52]));
                                    $product->setCasePackCode(trim($dataRow[53]));
                                    $product->setDosageDesc(trim($dataRow[55]));
                                    $product->setScCode(trim($dataRow[57]));
                                    $product->setScDesc(trim($dataRow[58]));
                                    $product->setStatusSap(trim($dataRow[59]));
                                    $product->setSapCreationDate(trim($dataRow[60]));*/

                                    $product->setAttributeSetId(4); // Attribute set id
                                    $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED); // Status on product
                                    $product->setWeight(0); // weight of product

                                    $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
                                    $product->setTaxClassId(0); // Tax class id
                                    $product->setTypeId('simple'); // type of product (simple/virtual/downloadable/configurable)
                                    $product->setPrice(0); // price of product
                                    $product->setStockData(
                                        array(
                                            'use_config_manage_stock' => 0,
                                            'manage_stock' => 1,
                                            'is_in_stock' => 1,
                                            'qty' => 0
                                        )
                                    );
                                    $product->setWebsiteIds(array(1));
                                    $categories = [4];
                                    $product->setCategoryIds($categories);
                                    try{
                                        //$product->save();
                                        //echo 'create new product'; exit();
                                        $this->_productRepository->save($product);

                                        if(in_array(trim($dataRow[34]), $configurable_skus)){
                                            //$_product = $this->_productRepository->get(trim($dataRow[1]));
                                            $associatedproducts[$dataRow[34]][] = $product->getId();;
                                            //$product->setVisibility(3); // visibilty of product (catalog / search / catalog, search / Not visible individually)
                                        } else {
                                            //$product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
                                        }
                                    }catch (\Exception $e) {
                                        //echo $e;
                                        $not_exists.='<br />' . __('SKU') . ' <b>"' . $sku . '"</b> ' . $e.' '.$counter;
                                    }
                                }
                                $import++;
                            } else {
                                $not_exists.='<br />' . __('SKU') . ' <b>"' . $sku . '"</b> ' . __('empty in row.'.$counter);
                            }

                        }

                        //echo '<pre>associatedproducts: '.print_r($associatedproducts, true).'</pre>';

                        $this->updateAssociatedProducts($associatedproducts);


                        //echo $import;

                        //exit();

                        if ($import > 0) {
                            $this->messageManager->addSuccess(__('Product Master imported successfully.') . $not_exists);

                            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                            $to_emails = explode(',', $this->helper->getToEmails());
                            if($to_emails){
                                $transport =
                                    $this->_transportBuilder
                                    ->setTemplateIdentifier('25') // Send the ID of Email template which is created in Admin panel
                                    ->setTemplateOptions(
                                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                                    )
                                    //->setTemplateVars(['data' => $postObject])
                                    ->setTemplateVars($templateVars)
                                    ->setFrom($sender)
                                    //->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
                                    ->addTo($to_emails)
                                    ->getTransport();
                                $transport->sendMessage();
                            }
                            $this->inlineTranslation->resume();
                        } else {
                            $this->messageManager->addError(__('No Product imported, data is not correct, check your file.') . $not_exists);
                        }
                    } else {
                        $this->messageManager->addError($mandatory_error);
                    }
                }

            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->doReindexing();
        $this->cachePrograme();

        $this->_redirect('*/*/import');
    }

    private function setAttributeValue($attribute_code, $attribute_values=[]){
        $all_options = $this->_eavConfig->getAttribute('catalog_product', $attribute_code)->getSource()->getAllOptions();
        $available_values_list = [];
        foreach ($all_options as $option) {
            if ($option['value'] > 0) {
                $available_values_list[$option['value']] = $option['label'];
            }
        }

        $attribute_id = $this->_eavAttribute->getIdByCode('catalog_product', $attribute_code);
        //echo 'attribute_id: '.$attribute_id.'<br />';
        $attribute = $this->_attributeFactory->load($attribute_id);

        foreach($attribute_values as $attribute_value){
            if(!in_array($attribute_value, $available_values_list)){

                //echo 'Dosage: '.$theraputic_cat.'<br />';

                $attribute->setData('option', array(
                    'value' => array(
                    'option' => array($attribute_value,$attribute_value)
                    )
                ));

                $attribute->save();

            }
        }
    }

    private function getAttributeValues($attribute_code){

        $available_values_list = [];

        $all_options = $this->_eavConfig->getAttribute('catalog_product', $attribute_code)->getSource()->getAllOptions();
        $available_values_list = [];
        foreach ($all_options as $option) {
            if ($option['value'] > 0) {
                $available_values_list[$option['value']] = $option['label'];
            }
        }

        return $available_values_list;

    }

    private function createConfigurableProduct($configurable_skus, $product_infos){

        //echo '<pre>'.print_r($configurable_skus, true).'</pre>';
        //echo '<pre>'.print_r($product_infos, true).'</pre>';

        foreach($product_infos as $product_info){

            if(in_array($product_info[34],$configurable_skus)){
                try{
                    $_product = $this->_productRepository->get($product_info[34]);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e){

                    //echo $product_info[2].'<br />';

                    $product = $this->_productFactory->create();

                    $product->setName($product_info[2]); // Set Product Name
                    $product->setTypeId('configurable'); // Set Product Type Id
                    $product->setAttributeSetId(4); // Set Attribute Set ID
                    $product->setSku($product_info[34]); // Set SKU
                    $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED); // Set Status
                    $product->setWeight(5); // Set Weight
                    $product->setTaxClassId(0); // Set Tax Class Id
                    $product->setWebsiteIds([1]); // Set Website Ids
                    $product->setVisibility(4);
                    $product->setCategoryIds([3]); // Assign Category Ids
                    //$product->setPrice(100); // Product Price
                    //$product->setImage('/configurable/test.jpg'); // Image Path
                    //$product->setSmallImage('/configurable/test.jpg'); // Small Image Path
                    //$product->setThumbnail('/configurable/test.jpg'); // Thumbnail Image Path
                    $product->setStockData(
                        [
                            'use_config_manage_stock' => 0, // Use Config Settings Checkbox
                            'manage_stock' => 1, // Manage Stock
                            'is_in_stock' => 1, // Stock Availability
                        ]
                    );
                    $strength_id = $product->getResource()->getAttribute('strength')->getId();
                    $pack_size_id = $product->getResource()->getAttribute('pack_size')->getId();
                    $product->getTypeInstance()->setUsedProductAttributeIds([$pack_size_id, $strength_id], $product);
                    $configurableAttributesData = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
                    $product->setCanSaveConfigurableAttributes(true);
                    $product->setConfigurableAttributesData($configurableAttributesData);
                    $configurableProductsData = [];
                    $product->setConfigurableProductsData($configurableProductsData);
                    try {
                        $product->save();

                        /*$result= [
                            'success' => true,
                            'msg' => ''
                        ];*/
                    } catch (Exception $ex) {
                        /*$result= [
                            'success' => false,
                            'msg' => $ex->getMessage()
                        ];*/

                        $this->messageManager->addError(__('Error while creating configurable product.') . $product_info[2]);
                    }

                }

            }

        }
        /*$result = [];


        return $result;*/
        /**/
    }

    private function updateAssociatedProducts($associatedproducts){

        //echo '<pre>'.print_r($associatedproducts, true).'</pre>';
        if($associatedproducts){
            //echo '<pre>'.print_r($associatedproducts, true).'</pre>';

            foreach($associatedproducts as $key=>$associatedproductSKUs){
                $_productparant = $this->_productRepository->get($key);

                $productId = $_productparant->getId();
                //$eassociatedProductIds = [2044, 2045]; // Add Your Associated Product Ids.
                //echo '<pre>Product Ids'.print_r($eassociatedProductIds, true).'</pre>';
                //$associatedProductIds = $associatedproductIds;

                $associatedProductIds = [];

                foreach($associatedproductSKUs as $sku){
                    $_product = $this->_productRepository->get($sku);
                    $associatedProductIds[] = $_product->getId();
                }

                //echo $key.'<br />';
                //echo '<pre>Product Ids'.print_r($associatedProductIds, true).'</pre>';
                try {
                    $configurable_product = $this->_productFactory->create()->load($productId);
                    //$configurable_product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId); // Load Configurable Product
                    $configurable_product->setAssociatedProductIds($associatedProductIds); // Setting Associated Products
                    $configurable_product->setCanSaveConfigurableAttributes(true);
                    $configurable_product->save();
                } catch (Exception $e) {
                    //print_r($e->getMessage());
                    $this->messageManager->addError(__('Error while updating Associated products.') . $key);
                }
            }
        }

    }

    private function doReindexing() {
        $indexerCollection = $this->indexCollection->create();
        $indexids = $indexerCollection->getAllIds();

        foreach ($indexids as $indexid){
            $indexidarray = $this->indexFactory->create()->load($indexid);

            //If you want reindex all use this code.
            //$indexidarray->reindexAll($indexid);

            //If you want to reindex one by one, use this code
            $indexidarray->reindexRow($indexid);
        }
    }

    private function cachePrograme() {

        $_cacheTypeList = $this->typeListInterface;

        $_cacheFrontendPool = $this->pool;

        $types = array('full_page');

        foreach ($types as $type) {
            $_cacheTypeList->cleanType($type);
        }

        foreach ($_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

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


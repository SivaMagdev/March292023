<?php

namespace Ecomm\Swatches\Block\ConfigurableProduct\Product\View\Type;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Framework\View\Result\PageFactory;

class Configurable extends \Magento\Framework\View\Element\AbstractBlock
{
    protected $assetRepo;
    protected $request;
    protected $formKey;
    protected $jsonEncoder;
    protected $jsonDecoder;
    protected $_productRepository;
    protected $_abstractProductBlock;
    protected $_groupCollection;
    protected $_configurableProductCollection;
    protected $listBlock;
    protected $_stockState;
    protected $customerSession;
    protected $customerRepository;
    protected $_eavConfig;
    protected $_storeManagerInterface;
    protected $cartHelper;
    protected $priceHelper;
    protected $custom_helper;
    protected $_helper;
    protected $wishlistCollection;
    protected $wishlistHelper;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Block\Product\AbstractProduct $abstractProductBlock,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableProductCollection,
        \Magento\Catalog\Block\Product\ListProduct $listBlock,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        CartHelper $cartHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Ecomm\Theme\Helper\Output $custom_helper,
        \Magento\Catalog\Helper\Output $helper,
        \Ecomm\Swatches\Model\Wishlist $wishlistCollection,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder
    ) {
        $this->assetRepo                        = $assetRepo;
        $this->request                          = $request;
        $this->formKey                          = $formKey;
        $this->jsonDecoder                      = $jsonDecoder;
        $this->jsonEncoder                      = $jsonEncoder;
        $this->_productRepository               = $productRepository;
        $this->_abstractProductBlock            = $abstractProductBlock;
        $this->_configurableProductCollection   = $configurableProductCollection;
        $this->listBlock                        = $listBlock;
        $this->_stockState                      = $stockState;
        $this->customerSession                  = $customerSession;
        $this->customerRepository               = $customerRepository;
        $this->_eavConfig                       = $eavConfig;
        $this->_storeManagerInterface           = $storeManagerInterface;
        $this->cartHelper                       = $cartHelper;
        $this->priceHelper                      = $priceHelper;
        $this->_helper                          = $helper;
        $this->custom_helper                    = $custom_helper;
        $this->httpContext                      = $httpContext;
        $this->wishlistCollection               = $wishlistCollection;
        $this->wishlistHelper                   = $wishlistHelper;
    }

    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }

    public function aroundGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        \Closure $proceed
    )
    {
        $sname = [];
        $sdescription = [];
        $sndcupc = [];
        $scasepack = [];
        $sbrand = [];
        $scoldchain = [];
        $sglutenfree = [];
        $slatexfree = [];
        $spreservativefree = [];
        $sdryfree = [];
        $sbarcoded = [];
        $sconcentration = [];
        $stotalcontent = [];
        $sshotdesc = [];
        $sshs = [];
        $stheraputiccat = [];
        $sfdarating = [];
        $squantity = [];
        $scustomprice = [];
        $swishlist = [];
        $swishlisturl = [];
        $saddtocarturl = [];
        $scustomoptions = [];
        $sshortdatedlable = [];
        $sadditioninfos = [];
        $swholesalerinfos = [];
        $ssupportivedocs = [];
        $parentid=[];

        $config = $proceed();
        $config = $this->jsonDecoder->decode($config);

        $customerid =  $this->customerSession->getId();

        //echo 'customerid: '.$customerid;

        if(!empty($customerid)) {
            $wishlist_collection = $this->wishlistCollection->getCustomerWishlist($this->httpContext->getValue('customer_id'));
            $wishlist_products = [];
            foreach($wishlist_collection as $wishlist_item) {
                $wishlist_products[] = $wishlist_item->getProductId();
            }
        } else {
            $wishlist_products = [];
        }

       /* echo '<pre>wishlist_products: '.print_r($wishlist_products, true).'</pre>';

        foreach ($subject->getAllowProducts() as $prod) {
            $id = $prod->getId();
            $product = $this->getProductById($id);

            echo $product->getId().'-'.in_array($product->getId(), $wishlist_products);
        }

        exit();*/

        foreach ($subject->getAllowProducts() as $prod) {
            $id = $prod->getId();
            $product = $this->getProductById($id);

            $addition_infos = '';
            $whole_saler_infos = '';
            $support_docs = '';

            $_parentproduct= $this->_configurableProductCollection->getParentIdsByChild($id);
            $sname[$id] = $product->getName();
            $sdescription[$id] = $product->getDescription();
            $sndcupc[$id] = $product->getSku();
            $scasepack[$id] =  $product->getCasePack();
            $sbrand[$id] =  $product->getResource()->getAttribute('brand_name')->setStoreId(0)->getFrontend()->getValue($product);
            $scoldchain[$id] = $product->getResource()->getAttribute('cold_chain')->setStoreId(0)->getFrontend()->getValue($product);
            $sglutenfree[$id] = $product->getResource()->getAttribute('gluten_free')->setStoreId(0)->getFrontend()->getValue($product);
            $slatexfree[$id] = $product->getResource()->getAttribute('latex_free')->setStoreId(0)->getFrontend()->getValue($product);
            $spreservativefree[$id] = $product->getResource()->getAttribute('preservative_free')->setStoreId(0)->getFrontend()->getValue($product);
            $sdryfree[$id] = $product->getResource()->getAttribute('dye_free')->setStoreId(0)->getFrontend()->getValue($product);
            $sbarcoded[$id] = $product->getResource()->getAttribute('bar_coded')->setStoreId(0)->getFrontend()->getValue($product);
            $sconcentration[$id] = $product->getConcentration();
            $stotalcontent[$id] = $product->getDrlDivision();
            $sshotdesc[$id] = $product->getShortDescription();
            $sshs[$id] =  $product->getSpecialHandlingStorage();
            $stheraputiccat[$id] =  $product->getResource()->getAttribute('theraputic_cat')->setStoreId(0)->getFrontend()->getValue($product);
            $sfdarating[$id] =  $product->getFdaRating();
            $squantity[$id] = $this->_stockState->getStockQty($product->getId(), 1);
            //$scustomprice[$id] = $this->priceHelper->currency($product->getFinalPrice(), true, false);
            $scustomprice[$id] = $this->_abstractProductBlock->getProductPrice($product);
            $saddtocarturl[$id] = $this->listBlock->getAddToCartUrl($product);
            $swishlist[$id] = in_array($product->getId(), $wishlist_products);
            $swishlisturl[$id] = $this->wishlistHelper->getAddParams($product);
            $scustomoptions[$id] = $this->getCustomOptions($product);
            if($this->getCustomOptions($product)) {
                $sshortdatedlable[$id] = 1;
            } else {
                $sshortdatedlable[$id] = 0;
            }

            $addition_infos = '<ul class="add-link-list">';
            if($this->_helper->productAttribute($product, $product->getLinkMedication(), 'link_medication')):
                $addition_infos .= '<li><a href="'.$this->_helper->productAttribute($product, $product->getLinkMedication(), 'link_medication').'">Media</a></li>';
            endif;

            if($this->_helper->productAttribute($product, $product->getLinkPrescribing(), 'link_prescribing')):
                $addition_infos .= '<li><a href="'.$this->_helper->productAttribute($product, $product->getLinkPrescribing(), 'link_prescribing').'">Prescribing Information</a></li>';
            endif;

            if($this->_helper->productAttribute($product, $product->getLinkDailymed(), 'link_dailymed')):
                $addition_infos .= '<li><a href="'.$this->_helper->productAttribute($product, $product->getLinkDailymed(), 'link_dailymed').'">HDMA</a>
                    </li>';
            endif;

            if($this->_helper->productAttribute($product, $product->getLinkMsds(), 'link_msds')):
                $addition_infos .= '<li><a href="'.$this->_helper->productAttribute($product, $product->getLinkMsds(), 'link_msds').'">SDS</a>
                    </li>';
            endif;
            $addition_infos .= '</ul>';

            $sadditioninfos[$id] = $addition_infos;

            $whole_saler_infos .= '<table>';
                if($this->_helper->productAttribute($product, $product->getAmerisourceBergen(), 'amerisource_bergen')):
                    $whole_saler_infos .= '<tr>';
                        $whole_saler_infos .= '<td>Amerisource Bergen (8): </td>';
                        $whole_saler_infos .= '<td>'.$this->_helper->productAttribute($product, $product->getAmerisourceBergen(), 'amerisource_bergen').'</td>';
                    $whole_saler_infos .= '</tr>';
                endif;

                if($this->_helper->productAttribute($product, $product->getCardinal(), 'cardinal')):
                    $whole_saler_infos .= '<tr>';
                        $whole_saler_infos .= '<td>Cardinal: </td>';
                        $whole_saler_infos .= '<td>'.$this->_helper->productAttribute($product, $product->getCardinal(), 'cardinal').'</td>';
                    $whole_saler_infos .= '</tr>';
                endif;

                if($this->_helper->productAttribute($product, $product->getMckesson(), 'mckesson')):
                    $whole_saler_infos .= '<tr>';
                        $whole_saler_infos .= '<td>McKesson: </td>';
                        $whole_saler_infos .= '<td>'.$this->_helper->productAttribute($product, $product->getMckesson(), 'mckesson').'</td>';
                    $whole_saler_infos .= '</tr>';
                endif;

                if($this->_helper->productAttribute($product, $product->getMD(), 'm_d')):
                    $whole_saler_infos .= '<tr>';
                        $whole_saler_infos .= '<td>M & D: </td>';
                        $whole_saler_infos .= '<td>'.$this->_helper->productAttribute($product, $product->getMD(), 'm_d').'</td>';
                    $whole_saler_infos .= '</tr>';
                endif;
            $whole_saler_infos .= '</table>';
            $swholesalerinfos[$id] = $whole_saler_infos;

            $ssupportivedocs[$id] = '';

            //$squantity[$id] = $this->_stockState->execute($product->getSku(), $product->getStore()->getWebsiteId());

            $parentid[$id]= $_parentproduct[0];
        }

        $config['sname'] = $sname;
        $config['sdescription'] = $sdescription;
        $config['sndcupc'] = $sndcupc;
        $config['scasepack'] = $scasepack;
        $config['sbrand'] = $sbrand;
        $config['scoldchain'] = $scoldchain;
        $config['sglutenfree'] = $sglutenfree;
        $config['slatexfree'] = $slatexfree;
        $config['spreservativefree'] = $spreservativefree;
        $config['sdryfree'] = $sdryfree;
        $config['sbarcoded'] = $sbarcoded;
        $config['sconcentration'] = $sconcentration;
        $config['stotalcontent'] = $stotalcontent;
        $config['sshotdesc'] = $sshotdesc;
        $config['sshs'] = $sshs;
        $config['stheraputiccat'] = $stheraputiccat;
        $config['sfdarating'] = $sfdarating;
        $config['squantity'] = $squantity;
        $config['scustomprice'] = $scustomprice;
        $config['swishlist'] = $swishlist;
        $config['swishlisturl'] = $swishlisturl;
        $config['saddtocarturl'] = $saddtocarturl;
        $config['scustomoptions'] = $scustomoptions;
        $config['sshortdatedlable'] = $sshortdatedlable;
        $config['sadditioninfos'] = $sadditioninfos;
        $config['swholesalerinfos'] = $swholesalerinfos;
        $config['ssupportivedocs'] = $ssupportivedocs;
        $config['sparentid']= $parentid;

        return $this->jsonEncoder->encode($config);
    }

    private function getCustomOptions($_product){

        if($_product->getOptions()) {

            $hide_addtocart = true;

            if($this->customerSession->getId()){
                $hide_addtocart = false;
                $customerData= $this->customerRepository->getById($this->customerSession->getId());

                $attribute = $this->_eavConfig->getAttribute('customer', 'application_status');
                $options = $attribute->getSource()->getAllOptions();
                $application_statuses = [];
                foreach ($options as $option) {
                    if ($option['value'] > 0) {
                        $application_statuses[$option['value']] = $option['label'];
                    }
                }
                $application_status = 0;
                $approved_id = array_search("Approved",$application_statuses);
                if($customerData->getCustomAttribute('application_status')){
                    $application_status = $customerData->getCustomAttribute('application_status')->getValue();
                }

                if($approved_id != $application_status){
                   $hide_addtocart = true;
                }
            }

            $addToCartUrl = $this->listBlock->getAddToCartUrl($_product);

            //$html = 'test: '.$hide_addtocart;
            $html = '';
            $html .= '<div class="shortdated-accordion">';
                $html .= '<div class="short-accordion">';
                    $html .= '<div class="short-accordion-head">';
                        $html .= '<div class="checkbox-header">';
                            $html .= '<label class="shortdated-container">Shortdated <input type="checkbox" checked="checked" id="short-container"><span class="checkmark"></span></label>';
                        $html .= '</div>';
                        $html .= '<div class="label-short-header"><i class="arrow-open"></i></div>';
                    $html .= '</div>';

                    $html .= '<div class="short-accordion-body default">';
                        $html .= '<div class="wrapper">';
                            $html .= '<div class="shortdated-table">';

                            foreach ($_product->getOptions() as $option) {

                                $i = 0;
                                foreach ($option->getValues() as $value) {

                                    $html .= '<form data-role="custom-tocart-form_'.$i. '" data-product-sku="'. $_product->getSku() .'" action="'. $addToCartUrl.'" method="post" id="custom_product_addtocart_form_'.$i. '">';

                                        $html .= '<input type="hidden" name="product" value="'. $_product->getId().'" />';

                                        $html .= '<input name="form_key" type="hidden" value="'.$this->formKey->getFormKey().'">';

                                        $html .= '<input type="hidden" class="radio admin__control-radio product-custom-option" name="options['. $value->getOptionId().']" id="options_'.$value->getOptionId(). ' ' . $value->getOptionId().'" value="'. $value->getOptionTypeId().'" data-selector="options['.$value->getOptionId().']" price="'.$value->getPrice().'">';

                                        if($i == 0):

                                            $html .= '<div class="row header">';
                                                $html .= '<div class="cell empty-head">head checkbox</div>';
                                                $html .= '<div class="cell">Batch No</div>';
                                                $html .= '<div class="cell">Expiration Date</div>';
                                                $html .= '<div class="cell">Strength</div>';
                                                $html .= '<div class="cell">Pack Size</div>';
                                                $html .= '<div class="cell">Case Pack</div>';
                                                if(!$hide_addtocart){
                                                    $html .= '<div class="cell">Price</div>';
                                                    $html .= '<div class="cell">Quantity in Units</div>';
                                                    $html .= '<div class="cell empty-head">empty</div>';
                                                }
                                            $html .= '</div>';

                                        endif;

                                        $current_date = strtotime(date('Y-m-d'));
                                        $expire_date = strtotime($value->getExpiryDate());
                                        if($expire_date > $current_date) {

                                            $row_class =  ($i % 2 === 0)? 'even-row' : '';

                                            $html .= '<div class="row '. $row_class . '">';
                                                $html .= '<div class="cell" data-title="head checkbox">';
                                                    $html .= '<label class="container"><input type="checkbox" checked="checked" /><span class="checkmark"></span></label>';
                                                $html .= '</div>';

                                                $html .= '<div class="cell" data-title="Batch No">' . $value->getTitle() . '</div>';

                                                $html .= '<div class="cell" data-title="Expiration Date">';
                                                    $diffmonth = 0;
                                                    //calculate month
                                                    $date1 = time(); //current date or any date
                                                    $date2 = strtotime($value->getExpiryDate()); //Future date

                                                    $datediff = $date2 - $date1;

                                                    $remining_days = round($datediff / (60 * 60 * 24));

                                                    $html .= '<span class="minus-date">'. $value->getExpiryDate().'</span>';
                                                    $html .= '<span class="minus-month">~'. $remining_days .' Day</span>';

                                                $html .= '</div>';

                                                if ($_additional = $this->custom_helper->getAdditionalData($_product)):
                                                    foreach ($_additional as $_data):
                                                        if($_data['code'] == 'strength'):
                                                            $html .= '<div class="cell" data-title="Strength">';
                                                                $html .= '<span class="pack-per-gram">'.$this->_helper->productAttribute($_product, $_data['value'], $_data['code']).'</span>';
                                                            $html .= '</div>';
                                                        endif;
                                                        if($_data['code'] == 'pack_size'):
                                                            $html .= '<div class="cell" data-title="Pack Size">';
                                                                $html .= '<span class="short-pack-size">'.$this->_helper->productAttribute($_product, $_data['value'], $_data['code']).'</span>';
                                                            $html .= '</div>';
                                                        endif;
                                                        if($_data['code'] == 'case_pack'):
                                                            $html .= '<div class="cell" data-title="Case Pack">';
                                                                $html .= '<span class="pack-size-d">'.$this->_helper->productAttribute($_product, $_data['value'], $_data['code']).'</span>';
                                                            $html .= '</div>';
                                                        endif;
                                                    endforeach;
                                                endif;

                                                if(!$hide_addtocart){

                                                    $params = array('_secure' => $this->request->isSecure());

                                                    $html .= '<div class="cell" data-title="Price">';
                                                        $html .= '<span class="pdp-price-value">'.number_format($value->getPrice(), 2, '.', '').'</span>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="cell" data-title="Quantity in Units">';
                                                        $html .= '<div class="control">';
                                                            $html .= '<span  class="num-minus decreaseQty"><img class="drl-image-minus-plus" src="'. $this->assetRepo->getUrlWithParams('images/drl-minus.png', $params).'" alt="Drl-Error"></span>';
                                                            $html .= '<input type="number" name="qty" id="qty" value="1" title="Quantity in Units" class="input-text qty" min="1" />';
                                                            $html .= '<span  class="num-add increaseQty" data-id="'.$i.'"><img class="drl-image-minus-plus" src="'. $this->assetRepo->getUrlWithParams('images/drl-plus.png', $params) .'" alt="Drl-Error"></span>';
                                                        $html .= '</div>';
                                                        $html .= '<span class="pdp-avil">Available : '. $value->getQuantity() .'</span>';
                                                    $html .= '</div>';

                                                    $html .= '<div class="cell" data-title="empty">';
                                                        $html .= '<span class="pdp-total-price">'. $this->priceHelper->currency($value->getPrice(), true, false) .'</span>';
                                                        $html .= '<button type="submit" title="Add to Cart" class="action primary tocart" id="custom-product-addtocart-button"><span>Add to Cart</span></button>';
                                                    $html .= '</div>';

                                                }
                                            $html .= '</div>';
                                        }

                                    $html .= '</form>';

                                    $i++;

                                }

                            }
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';

            return $html;
        } else {
            return false;
        }
    }
}
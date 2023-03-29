<?php
namespace Ecomm\HtmlToPdf\Model;

use Ecomm\HtmlToPdf\Api\ShippingPdfInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoicePdf implements ShippingPdfInterface {

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $directory_list;

    protected $_logo;

    protected $dompdf;

    protected $domoptions;

    protected $_filterProvider;

    protected $_storeManager;

    protected $_blockFactory;

    protected $resourceConnection;

	public function __construct(
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Dompdf $dompdf,
        Options $domoptions,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->_logo = $logo;
        $this->appEmulation = $appEmulation;
        $this->storeManager = $storeManager;
        $this->directory_list = $directory_list;
        $this->dompdf = $dompdf;
        $this->domoptions = $domoptions;
        $this->_filterProvider = $filterProvider;
        $this->_blockFactory = $blockFactory;
        $this->resourceConnection = $resourceConnection;
    }

	/**
	 * @return string
	 */
	public function getPdf($invoice_id)
	{
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['si' => 'ecomm_sap_order_invoice'], ['*'])
            ->where("si.m_invoice_id = :m_invoice_id");
            $bind = ['m_invoice_id'=>$invoice_id];

        //echo $select;
        $invoice_data_array = $connection->fetchAll($select, $bind);

        //echo '<pre>'.print_r($invoice_data_array, true).'</pre>';

        $html = '';
        $html .= $this->getStyle();

        $storeId = $this->storeManager->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);

        //$html .= '<img src="data:image/svg+xml;base64,' . base64_encode($this->_logo->getLogoSrc()) . '">';
        $html .= '<div><img src="'.$this->_logo->getLogoSrc().'" width="170px"></div>';
        $html .= '<p class="drl-invocie-document">Invoice Document</p>';

        //create order repository object for getting price type of order invoice
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderCollectionFactory = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory')->create();
        $orderRepository = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');

        if($invoice_data_array){
            foreach($invoice_data_array as $invoice_informations) {

                //for getting the price type of order invoice
                $orderCollection = $orderCollectionFactory->addFieldToFilter('sap_id', ['eq' => $invoice_informations['sap_id']]);
                foreach ($orderCollection as $collection) {
                    $orderId = $collection->getIncrementId();
                }
                $order = $orderRepository->get($orderId);
                foreach ($order->getAllVisibleItems() as $_item) {
                    $priceType = $_item->getPriceType();
                }

                $invoice_data_json = json_decode($invoice_informations['invoice_info']);
                $customer_account_number = '';

                foreach($invoice_data_json as $invoice_data) {
                    $itempartnerinformation = $invoice_data->itempartnerinformation;
                    $customer_account_number = $itempartnerinformation->Partnernumber;
                    $shipped_from = '';
                    $remit_address = [];
                    $bill_address = [];
                    $shipp_address = [];
                    $shipping_cost = 0;
                    $shipping_waiver = 0;

                    if(isset($invoice_data->Documentheadergeneraldata->Shipping_Cost_Waiver)){
                        $shipping_waiver = $invoice_data->Documentheadergeneraldata->Shipping_Cost_Waiver;
                    }

                    $rs_inc = 0;

                    foreach($invoice_data->DocumentHeaderPartnerInformation as $address_info) {

                        if($address_info->Partnerfunction == 'RE') {
                            $bill_address = $address_info;
                        } else if($address_info->Partnerfunction == 'WE') {
                            $shipp_address = $address_info;
                        } else if($address_info->Partnerfunction == 'RG') {
                            $shipped_from = $address_info->ShppiedFrom;
                        } else if($address_info->Partnerfunction == 'RS') {
                            $rs_inc++;
                            if($rs_inc > 1){
                                $remit_address = $address_info;
                            }
                        }

                    }

                    if(isset($invoice_data->DeliveryInformation->ShippingCost)){
                        $shipping_cost = $invoice_data->DeliveryInformation->ShippingCost;
                    }

                    $html .= '<div class="header-info">';
                        $html .= '<table>';
                        $html .= '<tr>';
                        $html .= '<td valign="top">';
                            $html .= '<div class="remit-to-address">';
                                $html .= '<h5>Remit To:</h5>';
                                if($remit_address) {
                                    $html .= '<p class="strong">'.$remit_address->Name1.'</p>';
                                    $Postalcode_array = explode("-", $remit_address->Postalcode);
                                    $html .= '<p>'.$Postalcode_array[1].'</p>';
                                    $html .= '<p>'.$remit_address->Street.'</p>';
                                    $html .= '<p>'.$remit_address->City.', '.$remit_address->State.' '.$remit_address->Postalcode.'</p>';
                                }
                                $html .= '<hr class="hr-drl-bold">';
                            $html .= '</div>';

                            if($bill_address) {
                                //echo '<pre>'.print_r($address_info, true).'</pre>';
                                $html .= '<div class="sold-to-address">';
                                    $html .= ' <p>'.$bill_address->Name1.'</p>';
                                    $html .= '<p>'.$bill_address->Street.'</p>';
                                    $html .= '<p>'.$bill_address->City.' '.$bill_address->State.' '.$bill_address->Postalcode.'</p>';
                                $html .= '</div>';
                            }
                            if($shipp_address) {
                                //echo '<pre>'.print_r($address_info, true).'</pre>';
                                $html .= '<div class="shipp-to-address">';
                                    $html .= '<h5>Ship-to Address</h5>';
                                    $html .= '<p>'.$shipp_address->Name1.'</p>';
                                    $html .= '<p>'.$shipp_address->Street.'</p>';
                                    $html .= '<p>'.$shipp_address->City.' '.$shipp_address->State.' '.$shipp_address->Postalcode.'</p>';
                                $html .= '</div>';
                            }
                        $html .= '</td>';
                        $html .= '<td valign="top">';
                            $invoice_number = '';
                            $invoice_date = '';
                            $credit_terms = '';
                            $po_number = '';
                            $po_date = '';
                            $date_shipped = '';
                            $drl_order_number = '';

                            foreach($invoice_data->Documentheaderreferencedata as $reference_info) {
                                //echo '<pre>'.print_r($reference_info, true).'</pre>';
                                //echo '-'.$reference_info->InvoiceNumber;


                                if($reference_info->InvoiceNumber == '009'){
                                    $invoice_number = $reference_info->CustomerPurchaseOrder;
                                    $invoice_date = date("m/d/Y", strtotime($reference_info->Date));
                                } else if($reference_info->InvoiceNumber == '001') {
                                    $po_number = $reference_info->CustomerPurchaseOrder;
                                    $po_date = date("m/d/Y", strtotime($reference_info->Date));
                                } else if($reference_info->InvoiceNumber == '002') {
                                    $drl_order_number = $reference_info->CustomerPurchaseOrder;
                                } else if($reference_info->InvoiceNumber == '012') {
                                    $date_shipped = date("m/d/Y", strtotime($reference_info->Date));
                                }

                            }

                            foreach($invoice_data->DocumentHeaderTermsofPayment as $reference_info) {
                                if($reference_info->Termsofpayment == '005'){
                                    $credit_terms = $reference_info->Textline;
                                }

                            }
                            $html .= '<h5 class="invoice-drl-head">Invoice</h5>';
                            $html .= '<div class="invoice-info">';
                                $html .= '<dl>';
                                    $html .= '<dt>Invoice Number / Invoice Date</dt>';
                                    $html .= '<dd>'.$invoice_number.' / '.$invoice_date.'</dd>';
                                $html .= '</dl>';
                                $html .= '<dl>';
                                    $html .= '<dt>Credit terms</dt>';
                                    $html .= '<dd>'.$credit_terms.'</dd>';
                                $html .= '</dl>';
                                $html .= '<dl>';
                                    $html .= '<dt>Purchase Order No. / Purchase Order Date</dt>';
                                    $html .= '<dd>'.$po_number.' / '.$po_date.'</dd>';
                                $html .= '</dl>';
                                $html .= '<dl>';
                                    $html .= '<dt>Date Shipped</dt>';
                                    $html .= '<dd>'.$date_shipped.'</dd>';
                                $html .= '</dl>';
                                $html .= '<dl>';
                                    $html .= '<dt>DRL Order number</dt>';
                                    $html .= '<dd>'.(int)$drl_order_number.'</dd>';
                                $html .= '</dl>';
                                $html .= '<dl>';
                                    $html .= '<dt>Customer Account No.</dt>';
                                    $html .= '<dd>'.(int)$customer_account_number.'</dd>';
                                $html .= '</dl>';
                                $html .= '<dl>';
                                    $html .= '<dt>Shipped From</dt>';
                                    $html .= '<dd>'.$shipped_from.'</dd>';
                                $html .= '</dl>';
                            $html .= '</div>';
                        $html .= '</td>';
                        $html .= '</tr>';
                        $html .= '</table>';
                    $html .= '</div>';
                    // $html .= '<br>';
                    $html .= '<div class="info-msg">';
                        $html .= '<p>Shortage/damages must be reported within 10 working days after receipt of goods.</p>';
                        $html .= '<p> Claims made after that will not be honored.</p>';
                    $html .= '</div>';
                    // $html .= '<br>';
                    $html .= '<div class="items-info">';
                        $html .= '<table>';
                            $html .= '<thead>';
                            $html .= ' <tr>';
                                    $html .= '<th rowspan="2">Item</th>';
                                    $html .= '<th rowspan="2">Product No.</th>';
                                    $html .= '<th rowspan="2">Qty Ordered</th>';
                                    $html .= '<th colspan="3" class="border-none">Product Description</th>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<th class="item-qty">Qty Shipped</th>';
                                    $html .= '<th class="item-price">Price</th>';
                                    $html .= '<th class="item-price-type">Price Type</th>';
                                    $html .= '<th class="item-total" align="center">Value</th>';
                                $html .= '</tr>';
                            $html .= '</thead>';
                            $total_invoice = 0;
                            $total_discount = 0;
                            $html .= '<tbody>';

                                if(is_array($invoice_data->DocumentItemGeneralData)) {
                                    foreach($invoice_data->DocumentItemGeneralData as $item_info){

                                        $rowspan = 2;
                                        if(isset($item_info->Fixedsurchargediscountontotalgross)){
                                            $rowspan = 3;
                                        }

                                        $html .= '<tr class="drl-top">';
                                            $html .= '<td rowspan="'.$rowspan.'">'.$item_info->Itemnumber.'</td>';
                                            $html .= '<td rowspan="2">'.$this->getProductSKU($item_info->MaterialID).'<br /> Batch '.$item_info->BatchID.'</td>';
                                            //$html .= '<td rowspan="2"><span class="item-qty">'.round($item_info->Quantity).' '.$item_info->Unitofmeasure.'</span></td>';
                                            $html .= '<td rowspan="2"><span class="item-qty">'.round($item_info->Quantity).' EA</span></td>';
                                            $html .= '<td colspan="3" class="border-none">
                                                <span class="item-title">';
                                                if(isset($item_info->Shorttext)) { $html .= $item_info->Shorttext; }
                                                $html .= '</span>';
                                            $html .= '</td>';
                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                                //$html .= '<td class="item-qty">'.round($item_info->Quantity).' '.$item_info->Unitofmeasure.'</td>';
                                                $html .= '<td class="item-qty">'.round($item_info->Quantity).' EA</td>';
                                                $html .= '<td class="item-price">'.number_format(abs($item_info->UnitPrice), 2,".", ",").'</td>';
                                                $html .= '<td class="item-price-type">'. $priceType . '</td>';
                                                $html .= '<td class="item-total" align="right">'.number_format($item_info->TotalValue, 2,".", ",").'</td>';
                                        $html .= '</tr>';
                                        if(isset($item_info->Fixedsurchargediscountontotalgross)){
                                            $html .= '<tr>';
                                                $html .= '<td>Discount Amount</td>';
                                                $html .= '<td class="item-qty"></td>';
                                                $html .= '<td></td>';
                                                $html .= '<td class="item-price">'.number_format((abs($item_info->Fixedsurchargediscountontotalgross)/round($item_info->Quantity)), 2,".", ",").'-</td>';
                                                $html .= '<td class="item-price-type">'. $priceType . '</td>';
                                                $html .= '<td class="item-total" align="right">'.number_format(abs($item_info->Fixedsurchargediscountontotalgross), 2,".", ",").'-</td>';
                                            $html .= '</tr>';
                                            $total_discount += abs($item_info->Fixedsurchargediscountontotalgross);
                                        }
                                        $total_invoice += $item_info->TotalValue;
                                    }
                                } else {
                                    $item_info = $invoice_data->DocumentItemGeneralData;
                                    $rowspan = 2;
                                    if(isset($item_info->Fixedsurchargediscountontotalgross)){
                                        $rowspan = 3;
                                    }
                                    $html .= '<tr class="drl-top">';
                                        $html .= '<td rowspan="'.$rowspan.'">'.$item_info->Itemnumber.'</td>';
                                        $html .= '<td rowspan="2">';
                                            $html .= $this->getProductSKU($item_info->MaterialID).'<br />
                                            Batch '.$item_info->BatchID;
                                        $html .= '</td>';
                                        //$html .= '<td rowspan="2">'.round($item_info->Quantity).' '.$item_info->Unitofmeasure.'</td>';
                                        $html .= '<td rowspan="2">'.round($item_info->Quantity).' EA</td>';
                                        $html .= '<td colspan="3" class="border-none">';
                                            if(isset($item_info->Shorttext)) { $html .= $item_info->Shorttext; }
                                        $html .= '</td>';
                                    $html .= '</tr>';
                                    $html .= '<tr>';
                                        //$html .= '<td class="item-qty">'.round($item_info->Quantity).' '.$item_info->Unitofmeasure.'</td>';
                                        $html .= '<td class="item-qty">'.round($item_info->Quantity).' EA</td>';
                                        $html .= '<td class="item-price">'.number_format(abs($item_info->UnitPrice), 2,".", ",").'</td>';
                                        $html .= '<td class="item-price-type">'. $priceType . '</td>';
                                        $html .= '<td class="item-total" align="right">'.number_format($item_info->TotalValue, 2,".", ",").'</td>';

                                    $html .= '</tr>';
                                    if(isset($item_info->Fixedsurchargediscountontotalgross)){
                                        $html .= '<tr>';
                                            $html .= '<td>Discount Amount</td>';
                                            $html .= '<td class="item-qty"></td>';
                                            $html .= '<td></td>';
                                            $html .= '<td class="item-price">'.number_format((abs($item_info->Fixedsurchargediscountontotalgross)/round($item_info->Quantity)), 2,".", ",").'-</td>';
                                            $html .= '<td class="item-price-type">'. $priceType . '</td>';
                                            $html .= '<td class="item-total" align="right">'.number_format(abs($item_info->Fixedsurchargediscountontotalgross), 2,".", ",").'-</td>';
                                        $html .= '</tr>';
                                        $total_discount += abs($item_info->Fixedsurchargediscountontotalgross);
                                    }
                                    //$html .= '<tr class="drl-line"></tr>';
                                    $total_invoice += $item_info->TotalValue;
                                }

                                $html .= '<tr class="drl-line drl-top">';
                                    //$html .= '<td> Total Items <br />IDN Processing <br />'.number_format($shipping_cost, 2,".", ",").'</td>';
                                    $html .= '<td>Total Items</td>';
                                    $html .= '<td colspan="5" align="right">'.number_format($total_invoice, 2,".", ",").'</td>';
                                $html .= '</tr>';
                            $html .= '</tbody>';
                            $html .= '<tfoot>';
                                if($total_discount > 0) {
                                $html .= '<tr>';
                                    $html .= '<td>Total Discount</td>';
                                    $html .= '<td colspan="5" align="right">'.number_format($total_discount, 2,".", ",").'-</td>';
                                    $total_invoice -= $total_discount;
                                $html .= '</tr>';
                                }
                                $html .= '<tr>';
                                    $html .= '<td>Processing Fee</td>';
                                    $html .= '<td colspan="5" align="right">'.number_format($shipping_cost, 2,".", ",").'</td>';
                                    $total_invoice += $shipping_cost;
                                $html .= '</tr>';
                                if($shipping_waiver > 0) {
                                $html .= '<tr>';
                                    $html .= '<td>Processing Fee Waiver</td>';
                                    $html .= '<td colspan="5" align="right">'.number_format($shipping_waiver, 2,".", ",").'-</td>';
                                    $total_invoice -= $shipping_waiver;
                                $html .= '</tr>';
                                }
                                $html .= '<tr>';
                                    $html .= '<td>Total Invoice</td>';
                                    $html .= '<td colspan="5" align="right">'.number_format($total_invoice, 2,".", ",").'</td>';
                                $html .= '</tr>';

                            $html .= '</tfoot>';
                        $html .= '</table>';
                    $html .= '</div>';
                }
                        $html .= '<h5 style="text-align: center; border-style: none; border-width: 1px; border-radius: 0px;margin-top: 2rem;margin-bottom: 2rem;line-height: 1.1;letter-spacing: 0px;
                        color: #4F4F4F;
                        opacity: 1;
                        font-weight: unset;
                        font-size: 24px;">Terms and Conditions</h5>';
                        $html .= '<div class="terms-and-conditions">';
                        $blockId = 'invoice_terms_condition';
                        $storeId = $this->storeManager->getStore()->getId();
                        $block = $this->_blockFactory->create();
                        $block->setStoreId($storeId)->load($blockId);
                        //$html .= $this->_view->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId('invoice_terms_condition')->toHtml();
                        $html .= $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent());
                    $html .= '</div>';

                $html .= '</div>';
            }
        }
        //echo $html; exit();

        $domoptions = $this->dompdf->getOptions();
        $domoptions->setDefaultFont('Courier');
        $domoptions->setIsHtml5ParserEnabled(true);
        $domoptions->setIsHtml5ParserEnabled(true);
        $this->domoptions->setIsRemoteEnabled(true);
        $this->domoptions->setIsPhpEnabled(true);
        $this->dompdf->setOptions($this->domoptions);
        //$this->dompdf->setPaper('A4', 'landscape');
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
        //$this->dompdf->stream();

        $pdf_string = $this->dompdf->output();

        return base64_encode($pdf_string);
	}

    public function getProductSKU($material_code){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');

        $_product = $productFactory->create()->loadByAttribute('material', trim($material_code));

        if($_product) {
            return $_product->getSku();
        } else {
            return $material_code;
        }

    }

    /**
     * @return string
     */
    public function getPdfAll($order_id)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['si' => 'ecomm_sap_order_invoice'], ['*'])
            ->where("si.magento_id = :magento_id");
            $bind = ['magento_id'=>$order_id];

        //echo $select;
        $invoice_data_array = $connection->fetchAll($select, $bind);

        //echo '<pre>'.print_r($invoice_data_array, true).'</pre>';

        $html = '';

        $html .= $this->getStyle();

        $storeId = $this->storeManager->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);

        //$html .= '<img src="data:image/svg+xml;base64,' . base64_encode($this->_logo->getLogoSrc()) . '">';

        $html .= '<div><img src="'.$this->_logo->getLogoSrc().'" width="170px"></div>';
        $html .= '<p class="drl-invocie-document">Invoice Document</p>';

        if($invoice_data_array){
            foreach($invoice_data_array as $invoice_informations) {
                $invoice_data_json = json_decode($invoice_informations['invoice_info']);
                $customer_account_number = '';

                foreach($invoice_data_json as $invoice_data) {
                    $itempartnerinformation = $invoice_data->itempartnerinformation;
                    $customer_account_number = $itempartnerinformation->Partnernumber;
                    $shipped_from = '';
                    $remit_address = [];
                    $bill_address = [];
                    $shipp_address = [];
                    $shipping_cost = 0;
                    $shipping_waiver = 0;

                    if(isset($invoice_data->Documentheadergeneraldata->Shipping_Cost_Waiver)){
                        $shipping_waiver = $invoice_data->Documentheadergeneraldata->Shipping_Cost_Waiver;
                    }

                    $rs_inc = 0;

                    foreach($invoice_data->DocumentHeaderPartnerInformation as $address_info) {

                        if($address_info->Partnerfunction == 'RE') {
                            $bill_address = $address_info;
                        } else if($address_info->Partnerfunction == 'WE') {
                            $shipp_address = $address_info;
                        } else if($address_info->Partnerfunction == 'RG') {
                            $shipped_from = $address_info->ShppiedFrom;
                        } else if($address_info->Partnerfunction == 'RS') {
                            $rs_inc++;
                            if($rs_inc > 1){
                                $remit_address = $address_info;
                            }
                        }

                    }

                    if(isset($invoice_data->DeliveryInformation->ShippingCost)){
                        $shipping_cost = $invoice_data->DeliveryInformation->ShippingCost;
                    }

                    $html .= '<div class="header-info">';
                        $html .= '<table>';
                        $html .= '<tr>';
                        $html .= '<td valign="top">';
                            $html .= '<div class="remit-to-address">';
                                $html .= '<h5>Remit To:</h5>';
                                if($remit_address) {
                                    $html .= '<p class="strong">'.$remit_address->Name1.'</p>';
                                    $Postalcode_array = explode("-", $remit_address->Postalcode);
                                    $html .= '<p>'.$Postalcode_array[1].'</p>';
                                    $html .= '<p>'.$remit_address->Street.'</p>';
                                    $html .= '<p>'.$remit_address->City.', '.$remit_address->State.' '.$remit_address->Postalcode.'</p>';
                                }
                                $html .= '<hr class="hr-drl-bold">';
                            $html .= '</div>';

                            if($bill_address) {
                                //echo '<pre>'.print_r($address_info, true).'</pre>';
                                $html .= '<div class="sold-to-address">';
                                    $html .= ' <p>'.$bill_address->Name1.'</p>';
                                    $html .= '<p>'.$bill_address->Street.'</p>';
                                    $html .= '<p>'.$bill_address->City.' '.$bill_address->State.' '.$bill_address->Postalcode.'</p>';
                                $html .= '</div>';
                            }
                            if($shipp_address) {
                                //echo '<pre>'.print_r($address_info, true).'</pre>';
                                $html .= '<div class="shipp-to-address">';
                                    $html .= '<h5>Ship-to Address</h5>';
                                    $html .= '<p>'.$shipp_address->Name1.'</p>';
                                    $html .= '<p>'.$shipp_address->Street.'</p>';
                                    $html .= '<p>'.$shipp_address->City.' '.$shipp_address->State.' '.$shipp_address->Postalcode.'</p>';
                                $html .= '</div>';
                            }
                        $html .= '</td>';
                        $html .= '<td valign="top">';
                            $invoice_number = '';
                            $invoice_date = '';
                            $credit_terms = '';
                            $po_number = '';
                            $po_date = '';
                            $date_shipped = '';
                            $drl_order_number = '';

                            foreach($invoice_data->Documentheaderreferencedata as $reference_info) {
                                //echo '<pre>'.print_r($reference_info, true).'</pre>';
                                //echo '-'.$reference_info->InvoiceNumber;


                                if($reference_info->InvoiceNumber == '009'){
                                    $invoice_number = $reference_info->CustomerPurchaseOrder;
                                    $invoice_date = date("m/d/Y", strtotime($reference_info->Date));
                                } else if($reference_info->InvoiceNumber == '001') {
                                    $po_number = $reference_info->CustomerPurchaseOrder;
                                    $po_date = date("m/d/Y", strtotime($reference_info->Date));
                                } else if($reference_info->InvoiceNumber == '002') {
                                    $drl_order_number = $reference_info->CustomerPurchaseOrder;
                                } else if($reference_info->InvoiceNumber == '012') {
                                    $date_shipped = date("m/d/Y", strtotime($reference_info->Date));
                                }

                            }

                            foreach($invoice_data->DocumentHeaderTermsofPayment as $reference_info) {
                                if($reference_info->Termsofpayment == '005'){
                                    $credit_terms = $reference_info->Textline;
                                }

                            }
                            $html .= '<h5 class="invoice-drl-head">Invoice</h5>';
                            $html .= '<div class="invoice-info">';
                                $html .= '<dl>';
                                    $html .= '<dt>Invoice Number / Invoice Date</dt>';
                                    $html .= '<dd>'.$invoice_number.' / '.$invoice_date.'</dd>';
                                $html .= '</dl>';
                                $html .= '<dl>';
                                    $html .= '<dt>Credit terms</dt>';
                                    $html .= '<dd>'.$credit_terms.'</dd>';
                                $html .= '</dl>';
                                $html .= '<dl>';
                                    $html .= '<dt>Purchase Order No. / Purchase Order Date</dt>';
                                    $html .= '<dd>'.$po_number.' / '.$po_date.'</dd>';
                                $html .= '</dl>';
                                $html .= '<dl>';
                                    $html .= '<dt>Date Shipped</dt>';
                                    $html .= '<dd>'.$date_shipped.'</dd>';
                                $html .= '</dl>';
                                $html .= '<dl>';
                                    $html .= '<dt>DRL Order number</dt>';
                                    $html .= '<dd>'.(int)$drl_order_number.'</dd>';
                                $html .= '</dl>';
                                $html .= '<dl>';
                                    $html .= '<dt>Customer Account No.</dt>';
                                    $html .= '<dd>'.(int)$customer_account_number.'</dd>';
                                $html .= '</dl>';
                                $html .= '<dl>';
                                    $html .= '<dt>Shipped From</dt>';
                                    $html .= '<dd>'.$shipped_from.'</dd>';
                                $html .= '</dl>';
                            $html .= '</div>';
                        $html .= '</td>';
                        $html .= '</tr>';
                        $html .= '</table>';
                    $html .= '</div>';
                    // $html .= '<br>';
                    $html .= '<div class="info-msg">';
                        $html .= '<p>Shortage/damages must be reported within 10 working days after receipt of goods.</p>';
                        $html .= '<p> Claims made after that will not be honored.</p>';
                    $html .= '</div>';
                    // $html .= '<br>';
                    $html .= '<div class="items-info">';
                        $html .= '<table>';
                            $html .= '<thead>';
                            $html .= ' <tr>';
                                    $html .= '<th rowspan="2">Item</th>';
                                    $html .= '<th rowspan="2">Product No.</th>';
                                    $html .= '<th rowspan="2">Qty Ordered</th>';
                                    $html .= '<th colspan="3" class="border-none">Product Description</th>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<th class="item-qty">Qty Shipped</th>';
                                    $html .= '<th class="item-price">Price</th>';
                                    $html .= '<th class="item-total" align="center">Value</th>';
                                $html .= '</tr>';
                            $html .= '</thead>';
                            $total_invoice = 0;
                            $total_discount = 0;
                            $html .= '<tbody>';

                                if(is_array($invoice_data->DocumentItemGeneralData)) {
                                    foreach($invoice_data->DocumentItemGeneralData as $item_info){
                                        $rowspan = 2;
                                        if(isset($item_info->Fixedsurchargediscountontotalgross)){
                                            $rowspan = 3;
                                        }

                                        $html .= '<tr class="drl-top">';
                                            $html .= '<td rowspan="'.$rowspan.'">'.$item_info->Itemnumber.'</td>';
                                            $html .= '<td rowspan="2">'.$this->getProductSKU($item_info->MaterialID).'<br /> Batch '.$item_info->BatchID.'</td>';
                                            //$html .= '<td rowspan="2"><span class="item-qty">'.round($item_info->Quantity).' '.$item_info->Unitofmeasure.'</span></td>';
                                            $html .= '<td rowspan="2"><span class="item-qty">'.round($item_info->Quantity).' EA</span></td>';
                                            $html .= '<td colspan="3" class="border-none">
                                                <span class="item-title">';
                                                if(isset($item_info->Shorttext)) { $html .= $item_info->Shorttext; }
                                                $html .= '</span>';
                                            $html .= '</td>';
                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                                //$html .= '<td class="item-qty">'.round($item_info->Quantity).' '.$item_info->Unitofmeasure.'</td>';
                                                $html .= '<td class="item-qty">'.round($item_info->Quantity).' EA</td>';
                                                $html .= '<td class="item-price">'.number_format(abs($item_info->UnitPrice), 2,".", ",").'</td>';
                                                $html .= '<td class="item-total" align="right">'.number_format($item_info->TotalValue, 2,".", ",").'</td>';
                                        $html .= '</tr>';
                                        if(isset($item_info->Fixedsurchargediscountontotalgross)){
                                            $html .= '<tr>';
                                                $html .= '<td>Discount Amount</td>';
                                                $html .= '<td class="item-qty"></td>';
                                                $html .= '<td></td>';
                                                $html .= '<td class="item-price">'.number_format((abs($item_info->Fixedsurchargediscountontotalgross)/round($item_info->Quantity)), 2,".", ",").'-</td>';
                                                $html .= '<td class="item-total" align="right">'.number_format(abs($item_info->Fixedsurchargediscountontotalgross), 2,".", ",").'-</td>';
                                            $html .= '</tr>';
                                            $total_discount += abs($item_info->Fixedsurchargediscountontotalgross);
                                        }
                                        $total_invoice += $item_info->TotalValue;
                                    }
                                } else {
                                    $item_info = $invoice_data->DocumentItemGeneralData;
                                    $rowspan = 2;
                                    if(isset($item_info->Fixedsurchargediscountontotalgross)){
                                        $rowspan = 3;
                                    }
                                    $html .= '<tr class="drl-top">';
                                        $html .= '<td rowspan="'.$rowspan.'">'.$item_info->Itemnumber.'</td>';
                                        $html .= '<td rowspan="2">';
                                            $html .= $this->getProductSKU($item_info->MaterialID).'<br />
                                            Batch '.$item_info->BatchID;
                                        $html .= '</td>';
                                        //$html .= '<td rowspan="2">'.round($item_info->Quantity).' '.$item_info->Unitofmeasure.'</td>';
                                        $html .= '<td rowspan="2">'.round($item_info->Quantity).' EA</td>';
                                        $html .= '<td colspan="3" class="border-none">';
                                            if(isset($item_info->Shorttext)) { $html .= $item_info->Shorttext; }
                                        $html .= '</td>';
                                    $html .= '</tr>';
                                    $html .= '<tr>';
                                        //$html .= '<td class="item-qty">'.round($item_info->Quantity).' '.$item_info->Unitofmeasure.'</td>';
                                        $html .= '<td class="item-qty">'.round($item_info->Quantity).' EA</td>';
                                        $html .= '<td class="item-price">'.number_format(abs($item_info->UnitPrice), 2,".", ",").'</td>';
                                        $html .= '<td class="item-total" align="right">'.number_format($item_info->TotalValue, 2,".", ",").'</td>';

                                    $html .= '</tr>';
                                    if(isset($item_info->Fixedsurchargediscountontotalgross)){
                                        $html .= '<tr>';
                                            $html .= '<td>Discount Amount</td>';
                                            $html .= '<td class="item-qty"></td>';
                                            $html .= '<td></td>';
                                            $html .= '<td class="item-price">'.number_format((abs($item_info->Fixedsurchargediscountontotalgross)/round($item_info->Quantity)), 2,".", ",").'-</td>';
                                            $html .= '<td class="item-total" align="right">'.number_format(abs($item_info->Fixedsurchargediscountontotalgross), 2,".", ",").'-</td>';
                                        $html .= '</tr>';
                                        $total_discount += abs($item_info->Fixedsurchargediscountontotalgross);
                                    }
                                    //$html .= '<tr class="drl-line"></tr>';
                                    $total_invoice += $item_info->TotalValue;
                                }

                                $html .= '<tr class="drl-line drl-top">';
                                    //$html .= '<td> Total Items <br />IDN Processing <br />'.number_format($shipping_cost, 2,".", ",").'</td>';
                                    $html .= '<td>Total Items</td>';
                                    $html .= '<td colspan="5" align="right">'.number_format($total_invoice, 2,".", ",").'</td>';
                                $html .= '</tr>';
                            $html .= '</tbody>';
                            $html .= '<tfoot>';
                                if($total_discount > 0) {
                                $html .= '<tr>';
                                    $html .= '<td>Total Discount</td>';
                                    $html .= '<td colspan="5" align="right">'.number_format($total_discount, 2,".", ",").'-</td>';
                                    $total_invoice -= $total_discount;
                                $html .= '</tr>';
                                }
                                $html .= '<tr>';
                                    $html .= '<td>Processing Fee</td>';
                                    $html .= '<td colspan="5" align="right">'.number_format($shipping_cost, 2,".", ",").'</td>';
                                    $total_invoice += $shipping_cost;
                                $html .= '</tr>';
                                if($shipping_waiver > 0) {
                                $html .= '<tr>';
                                    $html .= '<td>Processing Fee Waiver</td>';
                                    $html .= '<td colspan="5" align="right">'.number_format($shipping_waiver, 2,".", ",").'-</td>';
                                    $total_invoice -= $shipping_waiver;
                                $html .= '</tr>';
                                }
                                $html .= '<tr>';
                                    $html .= '<td>Total Invoice</td>';
                                    $html .= '<td colspan="5" align="right">'.number_format($total_invoice, 2,".", ",").'</td>';
                                $html .= '</tr>';

                            $html .= '</tfoot>';
                        $html .= '</table>';
                    $html .= '</div>';
                }
                        $html .= '<div class="terms-and-conditions">';
                        $blockId = 'invoice_terms_condition';
                        $storeId = $this->storeManager->getStore()->getId();
                        $block = $this->_blockFactory->create();
                        $block->setStoreId($storeId)->load($blockId);
                        //$html .= $this->_view->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId('invoice_terms_condition')->toHtml();
                        $html .= $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent());
                    $html .= '</div>';

                $html .= '</div>';
            }
        }

        //echo $html; exit();

        $this->domoptions->setIsRemoteEnabled(true);
        $this->domoptions->setIsPhpEnabled(true);
        $this->dompdf->setOptions($this->domoptions);
        //$this->dompdf->setPaper('A4', 'landscape');
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
        //$this->dompdf->stream();

        $pdf_string = $this->dompdf->output();

        return base64_encode($pdf_string);
    }

    private function getStyle()
    {

        /*return '<script type="text/php">
      if ( isset($this->dompdf) ) {
        $font = Font_Metrics::get_font("helvetica", "normal");
        $size = 18;
        $y = $this->dompdf->get_height() - 24;
        $x = $this->dompdf->get_width() - 15 - Font_Metrics::get_text_width("1/1", $font, $size);
        $this->dompdf->page_text($x, $y, "{PAGE_NUM}/{PAGE_COUNT}", $font, $size);
      }
    </script>*/
    return '<style>
        .items-info table>thead>tr>th{
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-size: 14px;
            font-family: "DRL Circular Book";
            border-bottom: none;
        }
        p.drl-invocie-document {
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-weight: bold;
            font-size: 16px;
            font-family: "DRL Circular Bold";
        }
        .remit-to-address h5,
        .header-info h5,
        .shipp-to-address h5 {
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-weight: unset;
            font-size: 16px;
            font-family: "DRL Circular Bold";
            font-weight:bold;
        }
        .header-info:before,
        .header-info:after,
        .info-msg:before,
        .info-msg:after,
        .items-info:after,
        .items-info:before,
        .terms-and-conditions:before,
        .terms-and-conditions:after,
        .remit-to-address:before,
        .remit-to-address:after,
        .sold-to-address:after,
        .sold-to-address:before,
        .shipp-to-address:after,
        .shipp-to-address:before
        {
            content: "";
            display: table;
            clear: both;
        }
        .remit-to-address h5, .header-info h5, .shipp-to-address h5 {
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-weight: bold;
            font-size: 16px;
            font-family: "DRL Circular Bold";
        }
        .header-info h5.invoice-drl-head {
            border: 1px solid;
            padding: 5px;
            background: lightgrey;
            margin-bottom: 0;
            margin-top: 0;
        }
        .remit-to-address .strong {
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-size: 14px;
            font-family: "DRL Circular Bold";
            font-weight: bold;
        }
        .header-info .invoice-info {
            padding: 20px 10px 20px 10px;
            border-left: 1px solid;
            border-right: 1px solid;
            border-bottom: 1px solid;
            width: 100%;
        }
        .header-info .invoice-info dl dt {
            font-weight: unset;
            letter-spacing: 0px;
            color: #9A9A9A;
            opacity: 1;
            font-size: 14px;
            font-family: "DRL Circular Book";
        }
        .header-info .invoice-info dl dd {
            font-weight: unset;
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-size: 16px;
            font-family: "DRL Circular Book";
        }
        dd {
            margin-bottom: 10px;
            margin-top: 0;
            margin-left: 0;
        }
        dl {
            margin-bottom: 15px;
            margin-top: 0;
        }
        dt {
            margin-bottom: 5px;
            margin-top: 0;
        }
        .info-msg {
            border: 1px solid;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .info-msg p {
            text-align: center;
            margin: 0;
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-family: "Circular Pro Book Italic";
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            max-width: 100%;
        }
        .items-info table>thead {
            border-top: 1px solid;
            border-bottom: 1px solid;
        }
        table>thead>tr>th, table>tbody>tr>th, table>tfoot>tr>th, table>thead>tr>td, table>tbody>tr>td, table>tfoot>tr>td {
            padding: 11px 10px;
        }
        table>thead>tr>th, table>thead>tr>td {
            vertical-align: bottom;
        }
        table th {
            text-align: left;
        }
        .drl-top {
            border-top: 1px solid;
        }
        .items-info table>tbody>tr>td {
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-size: 14px;
            font-family: "DRL Circular Book";
            border-bottom: none;
        }
        .items-info tbody tr.drl-line, .items-info table>tfoot, .items-info table>tfoot>tr {
            border-top: 1px solid;
            border-bottom: 1px solid;
        }
        .remit-to-address p,  .sold-to-address p,  .shipp-to-address p {
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-size: 16px;
            font-family: "DRL Circular Book";
        }
        .terms-and-conditions h5 {
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-weight: unset;
            font-size: 18px;
            font-family: "Circular Pro Book Italic";
            text-align: center;
        }
        .terms-and-conditions p {
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-size: 14px;
            font-family: "DRL Circular Book";
        }
        .block-title {
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-family: "DRL Circular Bold";
            font-size: 18px;
            padding-bottom: 10px;
            display: block;
        }
        *, *:before, *:after {
            box-sizing: border-box;
        }
        </style>';

    }
}
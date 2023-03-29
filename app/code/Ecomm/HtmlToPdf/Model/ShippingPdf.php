<?php
namespace Ecomm\HtmlToPdf\Model;

use Ecomm\HtmlToPdf\Api\ShippingPdfInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class ShippingPdf implements ShippingPdfInterface {

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

    protected $resourceConnection;
	public function __construct(
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Dompdf $dompdf,
        Options $domoptions,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->_logo = $logo;
        $this->appEmulation = $appEmulation;
        $this->storeManager = $storeManager;
        $this->directory_list = $directory_list;
        $this->dompdf = $dompdf;
        $this->domoptions = $domoptions;
        $this->resourceConnection = $resourceConnection;
    }

	/**
	 * @return string
	 */
	public function getPdf($shipment_id)
	{
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['sp' => 'ecomm_sap_order_asn'], ['sp.sap_id','sp.magento_id','spp.asn_info'])
            ->join(['spp' => 'ecomm_sap_order_asnprint'],'sp.delivery_id = spp.delivery_id')
            ->where("sp.m_delivery_id = :m_delivery_id")
            ->group("sp.delivery_id");
            $bind = ['m_delivery_id'=>$shipment_id];

        //echo $select;
        $shippment_data_array = $connection->fetchAll($select, $bind);

        //echo '<pre>'.print_r($shippment_data_array, true).'</pre>';

        $html = '';
        $html .= $this->getStyle();

        $storeId = $this->storeManager->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);

        /*$folderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
        $storeLogoPath = $this->scopeConfig->getValue(
            'design/header/logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $storeLogoPath;
        $logoUrl = $this->urlBuilder
                ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;*/

        //$html .= '<img src="data:image/svg+xml;base64,' . base64_encode($this->_logo->getLogoSrc()) . '">';
        //$html .= '<img src="data:image/svg+xml;base64,' . $this->_logo->getLogoSrc() . '">';

        //$html .= '<img src="data:image/svg+xml;base64,' . base64_encode($this->directory_list->getPath('static').'frontend/Ecommerce/Theme101/en_US/images/logo.svg') . '">';
        //$html .= '<svg>'.$this->_logo->getLogoSrc().'</svg>';
        //$html .= '<div><img src="'.$this->directory_list->getPath('static').'/frontend/Ecommerce/Theme101/en_US/images/logo.svg"></div>';
        //$html .= '<img src="data:image/svg+xml;base64,' . base64_encode($this->directory_list->getPath('static').'/frontend/Ecommerce/Theme101/en_US/images/logo.svg') . '">';

        $html .= '<div><img src="'.$this->_logo->getLogoSrc().'" width="170px"></div>';
        $html .= '<p class="drl-invocie-document">Shipment Document</p>';

        if($shippment_data_array){
            foreach($shippment_data_array as $shippment_informations) {
                $asn_info = str_replace('ns0:c','',$shippment_informations['asn_info']);
                $Jshippment = json_decode($asn_info);
                foreach($Jshippment as $PedigreeXML){
                    if(is_array($PedigreeXML->Pedigree)){
                        foreach($PedigreeXML->Pedigree as $Pedigree){

                            //echo '<pre>'.print_r($PedigreeXML->Pedigree->InitialPedigree, true).'</pre>';
                            $product_info = $Pedigree->InitialPedigree;
                            $TransactionInfo = $Pedigree->TransactionInfo;
                            $SenderInfo = $TransactionInfo->SenderInfo;
                            $RecipientInfo = $TransactionInfo->RecipientInfo;
                            $ndc_number = (array)$product_info->ProductInfo->ProductCode;
                            $dea_address = (array)$SenderInfo->BusinessAddress->AddressId;
                            $state_address = (array)$SenderInfo->ShippingAddress->AddressId;
                            $shipp_state_address = (array)$RecipientInfo->ShippingAddress->AddressId;
                            $AdditionalReferences = $Pedigree->AdditionalReferences->Ref1;
                            //echo '<pre>test:'.print_r($ndc_number['$'], true).'</pre>';

                            //$formated_sku = $block->getProductInfo($ndc_number['$']);
                            $ndc = $ndc_number['$'];
                            $sku = substr($ndc, 0, 5).'-'.substr($ndc, 5, 4).'-'.substr($ndc, 9);
                            $formated_sku = $sku;
                            $products[] = [
                                'product_mgf' => $product_info->ProductInfo->Manufacturer,
                                'product_name' => $product_info->ProductInfo->DrugName,
                                'product_sku' => $formated_sku,
                                'product_lot' => $product_info->ItemInfo->Lot,
                                'product_qty' => $product_info->ItemInfo->Quantity,
                                'product_expiry' => $product_info->ItemInfo->ExpirationDate
                            ];

                            $billing_address = [
                                'BusinessName' => $SenderInfo->BusinessAddress->BusinessName,
                                'Street' => $SenderInfo->BusinessAddress->Street1,
                                'City' => $SenderInfo->BusinessAddress->City,
                                'StateOrRegion' => $SenderInfo->BusinessAddress->StateOrRegion,
                                'PostalCode' => $SenderInfo->BusinessAddress->PostalCode,
                                'Country' => $SenderInfo->BusinessAddress->Country
                            ];
                            $shipping_address = [
                                'BusinessName' => $SenderInfo->ShippingAddress->BusinessName,
                                'Street' => $SenderInfo->ShippingAddress->Street1,
                                'City' => $SenderInfo->ShippingAddress->City,
                                'StateOrRegion' => $SenderInfo->ShippingAddress->StateOrRegion,
                                'PostalCode' => $SenderInfo->ShippingAddress->PostalCode,
                                'Country' => $SenderInfo->ShippingAddress->Country
                            ];

                            $rbilling_address = [
                                'BusinessName' => $RecipientInfo->BusinessAddress->BusinessName,
                                'Street' => $RecipientInfo->BusinessAddress->Street1,
                                'City' => $RecipientInfo->BusinessAddress->City,
                                'StateOrRegion' => $RecipientInfo->BusinessAddress->StateOrRegion,
                                'PostalCode' => $RecipientInfo->BusinessAddress->PostalCode,
                                'Country' => $RecipientInfo->BusinessAddress->Country,
                            ];
                            $rshipping_address = [
                                'BusinessName' => $RecipientInfo->ShippingAddress->BusinessName,
                                'Street' => $RecipientInfo->ShippingAddress->Street1,
                                'City' => $RecipientInfo->ShippingAddress->City,
                                'StateOrRegion' => $RecipientInfo->ShippingAddress->StateOrRegion,
                                'PostalCode' => $RecipientInfo->ShippingAddress->PostalCode,
                                'Country' => $RecipientInfo->ShippingAddress->Country,
                            ];

                            $shipmentPrintArray = [
                                'reference_number' => $TransactionInfo->TransactionIdentifier->Identifier,
                                'reference_date' => $TransactionInfo->TransactionDate,
                                'po_number' => $TransactionInfo->AltTransactionIdentifier->Identifier,
                                'products' => $products,
                                'seller_info' => [
                                    'BusinessAddress' => $billing_address,
                                    'ShippingAddress' => $shipping_address,
                                    'dea_licence' => $dea_address['$'],
                                    'state_licence' => $state_address['$'],
                                    'shipping_number' => $TransactionInfo->TransactionIdentifier->Identifier,
                                    'shipping_date' => $TransactionInfo->TransactionDate
                                ],
                                'recipient_info' => [
                                    'BusinessAddress' => $rbilling_address,
                                    'ShippingAddress' => $rshipping_address,
                                    'state_licence' => $shipp_state_address['$']
                                ],
                                'AdditionalReferences' => $AdditionalReferences
                            ];
                        }
                            $html .= '<main class="page-main">';
                            $html .= '<table class="item-ref-information">';
                               $html .= '<tr>';
                                    $html .= '<th class="drl-ship-he-se">Reference No.</th>';
                                    $html .= '<th class="drl-ship-he-se">Reference date</th>';
                                    $html .= '<td class="drl-ship-na-se">Purchase Order Number</td>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td class="drl-ship-na-se">'.$shipmentPrintArray['reference_number'].'</td>';
                                    $html .= '<td class="drl-ship-na-se">'.$shipmentPrintArray['reference_date'].'</td>';
                                    $html .= '<td class="drl-ship-na-se">'.$shipmentPrintArray['po_number'].'</td>';
                                $html .= '</tr>';
                            $html .= '</table>';
                            $SenderInfo = $shipmentPrintArray['seller_info'];
                            $RecipientInfo = $shipmentPrintArray['recipient_info'];
                            $html .= '<p class="drl-ship-name">'.$SenderInfo['BusinessAddress']['BusinessName'].' sold the following items to '.$RecipientInfo['BusinessAddress']['BusinessName'].'</p>';
                            $html .= '<table class="item-information">';
                                $html .= '<tr>';
                                    $html .= '<th colspan="6" class="drl-ship-head">Product Information</th>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td class="drl-ship-he-se">Manufacture name</td>';
                                    $html .= '<td class="drl-ship-he-se">Drug name</td>';
                                    $html .= '<td class="drl-ship-he-se">NDC</td>';
                                    $html .= '<td class="drl-ship-he-se">Lot number</td>';
                                    $html .= '<td class="drl-ship-he-se">Quantity</td>';
                                    $html .= '<td class="drl-ship-he-se">Expiration date</td>';
                                $html .= '</tr>';
                            foreach($shipmentPrintArray['products'] as $product) {
                                $html .= '<tr>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_mgf'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_name'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_sku'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_lot'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_qty'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_expiry'].'</td>';
                                $html .= '</tr>';
                            }
                            $html .= '</table>';
                            $html .= '<p></p>';
                            $html .= '<table>';
                                $html .= '<tr>';
                                    $html .= '<th colspan="2" class="drl-ship-head">History of drug sales and distribution</th>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td width="60%">';
                                        $html .= '<table class="seller-address-info">';
                                            $html .= '<tr>';
                                                $html .= '<th colspan="2">Seller Information</th>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Seller<br />Business address</td>';
                                                $html .= '<td>'.$SenderInfo['BusinessAddress']['BusinessName'].'<br />'.$SenderInfo['BusinessAddress']['Street'].'<br />'.$SenderInfo['BusinessAddress']['City'].', '.$SenderInfo['BusinessAddress']['StateOrRegion'].', '.$SenderInfo['BusinessAddress']['PostalCode'].', '.$SenderInfo['BusinessAddress']['Country'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Shipping address</td>';
                                                $html .= '<td>'.$SenderInfo['ShippingAddress']['BusinessName'].'<br />'.$SenderInfo['ShippingAddress']['Street'].'<br />'.$SenderInfo['ShippingAddress']['City'].', '.$SenderInfo['ShippingAddress']['StateOrRegion'].', '.$SenderInfo['ShippingAddress']['PostalCode'].', '.$SenderInfo['ShippingAddress']['Country'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>License number</td>';
                                                $html .= '<td>Seller DEA License - '.$SenderInfo['dea_licence'].', Ship From <br />State License - '.$SenderInfo['state_licence'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>ShippingNumber & Date</td>';
                                                $html .= '<td>'.$SenderInfo['shipping_number'].' '.$SenderInfo['shipping_date'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Transaction</td>';
                                                $html .= '<td>Sale</td>';
                                            $html .= '</tr>';
                                        $html .= '</table>';
                                    $html .= '</td>';
                                    $html .= '<td>';
                                        $html .= '<table class="seller-address-info">';
                                            $html .= '<tr>';
                                                $html .= '<th colspan="2">Recipient Information</th>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Recipient<br />Business address</td>';
                                                $html .= '<td>'.$RecipientInfo['BusinessAddress']['BusinessName'].'<br />'.$RecipientInfo['BusinessAddress']['Street'].'<br />'.$RecipientInfo['BusinessAddress']['City'].', '.$RecipientInfo['BusinessAddress']['StateOrRegion'].', '.$RecipientInfo['BusinessAddress']['PostalCode'].', '.$RecipientInfo['BusinessAddress']['Country'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Shipping address</td>';
                                                $html .= '<td>'.$RecipientInfo['ShippingAddress']['BusinessName'].'<br />'.$RecipientInfo['ShippingAddress']['Street'].'<br />'.$RecipientInfo['ShippingAddress']['City'].', '.$RecipientInfo['ShippingAddress']['StateOrRegion'].', '.$RecipientInfo['ShippingAddress']['PostalCode'].', '.$RecipientInfo['ShippingAddress']['Country'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>License number</td>';
                                                $html .= '<td>Ship To State License - '.$RecipientInfo['state_licence'].'</td>';
                                            $html .= '</tr>';
                                        $html .= '</table>';
                                    $html .= '</td>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td colspan="2" class="drl-ship-footer">'.$shipmentPrintArray['AdditionalReferences'].'</td>';
                                $html .= '</tr>';
                            $html .= '</table>';
                            $html .= '</main>';

                    } else {
                        //echo '<pre>'.print_r($PedigreeXML->Pedigree->InitialPedigree, true).'</pre>';
                        $product_info = $PedigreeXML->Pedigree->InitialPedigree;
                        $TransactionInfo = $PedigreeXML->Pedigree->TransactionInfo;
                        $SenderInfo = $TransactionInfo->SenderInfo;
                        $RecipientInfo = $TransactionInfo->RecipientInfo;
                        $ndc_number = (array)$product_info->ProductInfo->ProductCode;
                        $dea_address = (array)$SenderInfo->BusinessAddress->AddressId;
                        $state_address = (array)$SenderInfo->ShippingAddress->AddressId;
                        $shipp_state_address = (array)$RecipientInfo->ShippingAddress->AddressId;
                        $AdditionalReferences = $PedigreeXML->Pedigree->AdditionalReferences->Ref1;
                        //echo '<pre>'.print_r($dea_address['$'], true).'</pre>';
                        //$product_infos = $block->getProductInfo($ndc_number['$']);
                        $ndc = $ndc_number['$'];
                        $sku = substr($ndc, 0, 5).'-'.substr($ndc, 5, 4).'-'.substr($ndc, 9);
                        $formated_sku = $sku;
                        $products[] = [
                            'product_mgf' => $product_info->ProductInfo->Manufacturer,
                            'product_name' => $product_info->ProductInfo->DrugName,
                            'product_sku' => $formated_sku,
                            'product_lot' => $product_info->ItemInfo->Lot,
                            'product_qty' => $product_info->ItemInfo->Quantity,
                            'product_expiry' => $product_info->ItemInfo->ExpirationDate
                        ];

                        $billing_address = [
                            'BusinessName' => $SenderInfo->BusinessAddress->BusinessName,
                            'Street' => $SenderInfo->BusinessAddress->Street1,
                            'City' => $SenderInfo->BusinessAddress->City,
                            'StateOrRegion' => $SenderInfo->BusinessAddress->StateOrRegion,
                            'PostalCode' => $SenderInfo->BusinessAddress->PostalCode,
                            'Country' => $SenderInfo->BusinessAddress->Country
                        ];
                        $shipping_address = [
                            'BusinessName' => $SenderInfo->ShippingAddress->BusinessName,
                            'Street' => $SenderInfo->ShippingAddress->Street1,
                            'City' => $SenderInfo->ShippingAddress->City,
                            'StateOrRegion' => $SenderInfo->ShippingAddress->StateOrRegion,
                            'PostalCode' => $SenderInfo->ShippingAddress->PostalCode,
                            'Country' => $SenderInfo->ShippingAddress->Country
                        ];

                        $rbilling_address = [
                            'BusinessName' => $RecipientInfo->BusinessAddress->BusinessName,
                            'Street' => $RecipientInfo->BusinessAddress->Street1,
                            'City' => $RecipientInfo->BusinessAddress->City,
                            'StateOrRegion' => $RecipientInfo->BusinessAddress->StateOrRegion,
                            'PostalCode' => $RecipientInfo->BusinessAddress->PostalCode,
                            'Country' => $RecipientInfo->BusinessAddress->Country,
                        ];
                        $rshipping_address = [
                            'BusinessName' => $RecipientInfo->ShippingAddress->BusinessName,
                            'Street' => $RecipientInfo->ShippingAddress->Street1,
                            'City' => $RecipientInfo->ShippingAddress->City,
                            'StateOrRegion' => $RecipientInfo->ShippingAddress->StateOrRegion,
                            'PostalCode' => $RecipientInfo->ShippingAddress->PostalCode,
                            'Country' => $RecipientInfo->ShippingAddress->Country,
                        ];

                        $shipmentPrintArray = [
                            'reference_number' => $TransactionInfo->TransactionIdentifier->Identifier,
                            'reference_date' => $TransactionInfo->TransactionDate,
                            'po_number' => $TransactionInfo->AltTransactionIdentifier->Identifier,
                            'products' => $products,
                            'seller_info' => [
                                'BusinessAddress' => $billing_address,
                                'ShippingAddress' => $shipping_address,
                                'dea_licence' => $dea_address['$'],
                                'state_licence' => $state_address['$'],
                                'shipping_number' => $TransactionInfo->TransactionIdentifier->Identifier,
                                'shipping_date' => $TransactionInfo->TransactionDate
                            ],
                            'recipient_info' => [
                                'BusinessAddress' => $rbilling_address,
                                'ShippingAddress' => $rshipping_address,
                                'state_licence' => $shipp_state_address['$']
                            ],
                            'AdditionalReferences' => $AdditionalReferences
                        ];
                        $html .= '<main class="page-main">';
                            $html .= '<table class="item-ref-information">';
                               $html .= '<tr>';
                                    $html .= '<th class="drl-ship-he-se">Reference No.</th>';
                                    $html .= '<th class="drl-ship-he-se">Reference date</th>';
                                    $html .= '<td class="drl-ship-na-se">Purchase Order Number</td>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td class="drl-ship-na-se">'.$shipmentPrintArray['reference_number'].'</td>';
                                    $html .= '<td class="drl-ship-na-se">'.$shipmentPrintArray['reference_date'].'</td>';
                                    $html .= '<td class="drl-ship-na-se">'.$shipmentPrintArray['po_number'].'</td>';
                                $html .= '</tr>';
                            $html .= '</table>';
                            $SenderInfo = $shipmentPrintArray['seller_info'];
                            $RecipientInfo = $shipmentPrintArray['recipient_info'];
                            $html .= '<p class="drl-ship-name">'.$SenderInfo['BusinessAddress']['BusinessName'].' sold the following items to '.$RecipientInfo['BusinessAddress']['BusinessName'].'</p>';
                            $html .= '<table class="item-information">';
                                $html .= '<tr>';
                                    $html .= '<th colspan="6" class="drl-ship-head">Product Information</th>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td class="drl-ship-he-se">Manufacture name</td>';
                                    $html .= '<td class="drl-ship-he-se">Drug name</td>';
                                    $html .= '<td class="drl-ship-he-se">NDC</td>';
                                    $html .= '<td class="drl-ship-he-se">Lot number</td>';
                                    $html .= '<td class="drl-ship-he-se">Quantity</td>';
                                    $html .= '<td class="drl-ship-he-se">Expiration date</td>';
                                $html .= '</tr>';
                            foreach($shipmentPrintArray['products'] as $product) {
                                $html .= '<tr>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_mgf'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_name'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_sku'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_lot'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_qty'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_expiry'].'</td>';
                                $html .= '</tr>';
                            }
                            $html .= '</table>';
                            $html .= '<p></p>';
                            $html .= '<table>';
                                $html .= '<tr>';
                                    $html .= '<th colspan="2" class="drl-ship-head">History of drug sales and distribution</th>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td width="60%">';
                                        $html .= '<table class="seller-address-info">';
                                            $html .= '<tr>';
                                                $html .= '<th colspan="2">Seller Information</th>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Seller<br />Business address</td>';
                                                $html .= '<td>'.$SenderInfo['BusinessAddress']['BusinessName'].'<br />'.$SenderInfo['BusinessAddress']['Street'].'<br />'.$SenderInfo['BusinessAddress']['City'].', '.$SenderInfo['BusinessAddress']['StateOrRegion'].', '.$SenderInfo['BusinessAddress']['PostalCode'].', '.$SenderInfo['BusinessAddress']['Country'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Shipping address</td>';
                                                $html .= '<td>'.$SenderInfo['ShippingAddress']['BusinessName'].'<br />'.$SenderInfo['ShippingAddress']['Street'].'<br />'.$SenderInfo['ShippingAddress']['City'].', '.$SenderInfo['ShippingAddress']['StateOrRegion'].', '.$SenderInfo['ShippingAddress']['PostalCode'].', '.$SenderInfo['ShippingAddress']['Country'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>License number</td>';
                                                $html .= '<td>Seller DEA License - '.$SenderInfo['dea_licence'].', Ship From <br />State License - '.$SenderInfo['state_licence'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>ShippingNumber & Date</td>';
                                                $html .= '<td>'.$SenderInfo['shipping_number'].' '.$SenderInfo['shipping_date'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Transaction</td>';
                                                $html .= '<td>Sale</td>';
                                            $html .= '</tr>';
                                        $html .= '</table>';
                                    $html .= '</td>';
                                    $html .= '<td>';
                                        $html .= '<table class="seller-address-info">';
                                            $html .= '<tr>';
                                                $html .= '<th colspan="2">Recipient Information</th>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Recipient<br />Business address</td>';
                                                $html .= '<td>'.$RecipientInfo['BusinessAddress']['BusinessName'].'<br />'.$RecipientInfo['BusinessAddress']['Street'].'<br />'.$RecipientInfo['BusinessAddress']['City'].', '.$RecipientInfo['BusinessAddress']['StateOrRegion'].', '.$RecipientInfo['BusinessAddress']['PostalCode'].', '.$RecipientInfo['BusinessAddress']['Country'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Shipping address</td>';
                                                $html .= '<td>'.$RecipientInfo['ShippingAddress']['BusinessName'].'<br />'.$RecipientInfo['ShippingAddress']['Street'].'<br />'.$RecipientInfo['ShippingAddress']['City'].', '.$RecipientInfo['ShippingAddress']['StateOrRegion'].', '.$RecipientInfo['ShippingAddress']['PostalCode'].', '.$RecipientInfo['ShippingAddress']['Country'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>License number</td>';
                                                $html .= '<td>Ship To State License - '.$RecipientInfo['state_licence'].'</td>';
                                            $html .= '</tr>';
                                        $html .= '</table>';
                                    $html .= '</td>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td colspan="2" class="drl-ship-footer">'.$shipmentPrintArray['AdditionalReferences'].'</td>';
                                $html .= '</tr>';
                            $html .= '</table>';
                        $html .= '</main>';
                    }
                }
            }
        }
        //echo $html; exit();

        $domoptions = $this->dompdf->getOptions();
        $domoptions->setDefaultFont('Courier');
        $domoptions->setIsHtml5ParserEnabled(true);
        $this->domoptions->setIsRemoteEnabled(true);
        $this->dompdf->setOptions($this->domoptions);
        $this->dompdf->setPaper('A4', 'landscape');
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
        //$this->dompdf->stream();

        $pdf_string = $this->dompdf->output();

        return base64_encode($pdf_string);

        /*$dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->setDefaultFont('Courier');
        $options->setIsHtml5ParserEnabled(true);
        $options->isRemoteEnabled(true);
        $dompdf->setOptions($options);

		$dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        //$dompdf->stream();

        $pdf_string =   $dompdf->output();

        return base64_encode($pdf_string);*/
	}

    /**
     * @return string
     */
    public function getPdfAll($order_id)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['sp' => 'ecomm_sap_order_asn'], ['sp.sap_id','sp.magento_id','spp.asn_info'])
            ->join(['spp' => 'ecomm_sap_order_asnprint'],'sp.delivery_id = spp.delivery_id')
            ->where("sp.magento_id = :magento_id")
            ->group("sp.delivery_id");
            $bind = ['magento_id'=>$order_id];

        //echo $select;
        $shippment_data_array = $connection->fetchAll($select, $bind);

        //echo '<pre>'.print_r($shippment_data_array, true).'</pre>';

        $html = '';
        $html .= $this->getStyle();

        $storeId = $this->storeManager->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);

        //$html .= '<img src="data:image/svg+xml;base64,' . base64_encode($this->_logo->getLogoSrc()) . '">';
        $html .= '<div><img src="'.$this->_logo->getLogoSrc().'" width="170px"></div>';
        $html .= '<p class="drl-invocie-document">Shipment Document</p>';

        if($shippment_data_array){
            foreach($shippment_data_array as $shippment_informations) {
                $asn_info = str_replace('ns0:c','',$shippment_informations['asn_info']);
                $Jshippment = json_decode($asn_info);
                foreach($Jshippment as $PedigreeXML){
                    if(is_array($PedigreeXML->Pedigree)){
                        foreach($PedigreeXML->Pedigree as $Pedigree){

                            //echo '<pre>'.print_r($PedigreeXML->Pedigree->InitialPedigree, true).'</pre>';
                            $product_info = $Pedigree->InitialPedigree;
                            $TransactionInfo = $Pedigree->TransactionInfo;
                            $SenderInfo = $TransactionInfo->SenderInfo;
                            $RecipientInfo = $TransactionInfo->RecipientInfo;
                            $ndc_number = (array)$product_info->ProductInfo->ProductCode;
                            $dea_address = (array)$SenderInfo->BusinessAddress->AddressId;
                            $state_address = (array)$SenderInfo->ShippingAddress->AddressId;
                            $shipp_state_address = (array)$RecipientInfo->ShippingAddress->AddressId;
                            $AdditionalReferences = $Pedigree->AdditionalReferences->Ref1;
                            //echo '<pre>test:'.print_r($ndc_number['$'], true).'</pre>';

                            //$formated_sku = $block->getProductInfo($ndc_number['$']);
                            $ndc = $ndc_number['$'];
                            $sku = substr($ndc, 0, 5).'-'.substr($ndc, 5, 4).'-'.substr($ndc, 9);
                            $formated_sku = $sku;

                            $products[] = [
                                'product_mgf' => $product_info->ProductInfo->Manufacturer,
                                'product_name' => $product_info->ProductInfo->DrugName,
                                'product_sku' => $formated_sku,
                                'product_lot' => $product_info->ItemInfo->Lot,
                                'product_qty' => $product_info->ItemInfo->Quantity,
                                'product_expiry' => $product_info->ItemInfo->ExpirationDate
                            ];

                            $billing_address = [
                                'BusinessName' => $SenderInfo->BusinessAddress->BusinessName,
                                'Street' => $SenderInfo->BusinessAddress->Street1,
                                'City' => $SenderInfo->BusinessAddress->City,
                                'StateOrRegion' => $SenderInfo->BusinessAddress->StateOrRegion,
                                'PostalCode' => $SenderInfo->BusinessAddress->PostalCode,
                                'Country' => $SenderInfo->BusinessAddress->Country
                            ];
                            $shipping_address = [
                                'BusinessName' => $SenderInfo->ShippingAddress->BusinessName,
                                'Street' => $SenderInfo->ShippingAddress->Street1,
                                'City' => $SenderInfo->ShippingAddress->City,
                                'StateOrRegion' => $SenderInfo->ShippingAddress->StateOrRegion,
                                'PostalCode' => $SenderInfo->ShippingAddress->PostalCode,
                                'Country' => $SenderInfo->ShippingAddress->Country
                            ];

                            $rbilling_address = [
                                'BusinessName' => $RecipientInfo->BusinessAddress->BusinessName,
                                'Street' => $RecipientInfo->BusinessAddress->Street1,
                                'City' => $RecipientInfo->BusinessAddress->City,
                                'StateOrRegion' => $RecipientInfo->BusinessAddress->StateOrRegion,
                                'PostalCode' => $RecipientInfo->BusinessAddress->PostalCode,
                                'Country' => $RecipientInfo->BusinessAddress->Country,
                            ];
                            $rshipping_address = [
                                'BusinessName' => $RecipientInfo->ShippingAddress->BusinessName,
                                'Street' => $RecipientInfo->ShippingAddress->Street1,
                                'City' => $RecipientInfo->ShippingAddress->City,
                                'StateOrRegion' => $RecipientInfo->ShippingAddress->StateOrRegion,
                                'PostalCode' => $RecipientInfo->ShippingAddress->PostalCode,
                                'Country' => $RecipientInfo->ShippingAddress->Country,
                            ];

                            $shipmentPrintArray = [
                                'reference_number' => $TransactionInfo->TransactionIdentifier->Identifier,
                                'reference_date' => $TransactionInfo->TransactionDate,
                                'po_number' => $TransactionInfo->AltTransactionIdentifier->Identifier,
                                'products' => $products,
                                'seller_info' => [
                                    'BusinessAddress' => $billing_address,
                                    'ShippingAddress' => $shipping_address,
                                    'dea_licence' => $dea_address['$'],
                                    'state_licence' => $state_address['$'],
                                    'shipping_number' => $TransactionInfo->TransactionIdentifier->Identifier,
                                    'shipping_date' => $TransactionInfo->TransactionDate
                                ],
                                'recipient_info' => [
                                    'BusinessAddress' => $rbilling_address,
                                    'ShippingAddress' => $rshipping_address,
                                    'state_licence' => $shipp_state_address['$']
                                ],
                                'AdditionalReferences' => $AdditionalReferences
                            ];
                        }
                        $html .= '<main class="page-main">';
                        $html .= '<table class="item-ref-information">';
                           $html .= '<tr>';
                                $html .= '<th class="drl-ship-he-se">Reference No.</th>';
                                $html .= '<th class="drl-ship-he-se">Reference date</th>';
                                $html .= '<td class="drl-ship-na-se">Purchase Order Number</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td class="drl-ship-na-se">'.$shipmentPrintArray['reference_number'].'</td>';
                                $html .= '<td class="drl-ship-na-se">'.$shipmentPrintArray['reference_date'].'</td>';
                                $html .= '<td class="drl-ship-na-se">'.$shipmentPrintArray['po_number'].'</td>';
                            $html .= '</tr>';
                        $html .= '</table>';
                        $SenderInfo = $shipmentPrintArray['seller_info'];
                        $RecipientInfo = $shipmentPrintArray['recipient_info'];
                        $html .= '<p class="drl-ship-name">'.$SenderInfo['BusinessAddress']['BusinessName'].' sold the following items to '.$RecipientInfo['BusinessAddress']['BusinessName'].'</p>';
                        $html .= '<table class="item-information">';
                            $html .= '<tr>';
                                $html .= '<th colspan="6" class="drl-ship-head">Product Information</th>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td class="drl-ship-he-se">Manufacture name</td>';
                                $html .= '<td class="drl-ship-he-se">Drug name</td>';
                                $html .= '<td class="drl-ship-he-se">NDC</td>';
                                $html .= '<td class="drl-ship-he-se">Lot number</td>';
                                $html .= '<td class="drl-ship-he-se">Quantity</td>';
                                $html .= '<td class="drl-ship-he-se">Expiration date</td>';
                            $html .= '</tr>';
                        foreach($shipmentPrintArray['products'] as $product) {
                            $html .= '<tr>';
                                $html .= '<td class="drl-ship-he-se">'.$product['product_mgf'].'</td>';
                                $html .= '<td class="drl-ship-he-se">'.$product['product_name'].'</td>';
                                $html .= '<td class="drl-ship-he-se">'.$product['product_sku'].'</td>';
                                $html .= '<td class="drl-ship-he-se">'.$product['product_lot'].'</td>';
                                $html .= '<td class="drl-ship-he-se">'.$product['product_qty'].'</td>';
                                $html .= '<td class="drl-ship-he-se">'.$product['product_expiry'].'</td>';
                            $html .= '</tr>';
                        }
                        $html .= '</table>';
                        $html .= '<p></p>';
                        $html .= '<table>';
                            $html .= '<tr>';
                                $html .= '<th colspan="2" class="drl-ship-head">History of drug sales and distribution</th>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td width="60%">';
                                    $html .= '<table class="seller-address-info">';
                                        $html .= '<tr>';
                                            $html .= '<th colspan="2">Seller Information</th>';
                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                            $html .= '<td>Seller<br />Business address</td>';
                                            $html .= '<td>'.$SenderInfo['BusinessAddress']['BusinessName'].'<br />'.$SenderInfo['BusinessAddress']['Street'].'<br />'.$SenderInfo['BusinessAddress']['City'].', '.$SenderInfo['BusinessAddress']['StateOrRegion'].', '.$SenderInfo['BusinessAddress']['PostalCode'].', '.$SenderInfo['BusinessAddress']['Country'].'</td>';
                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                            $html .= '<td>Shipping address</td>';
                                            $html .= '<td>'.$SenderInfo['ShippingAddress']['BusinessName'].'<br />'.$SenderInfo['ShippingAddress']['Street'].'<br />'.$SenderInfo['ShippingAddress']['City'].', '.$SenderInfo['ShippingAddress']['StateOrRegion'].', '.$SenderInfo['ShippingAddress']['PostalCode'].', '.$SenderInfo['ShippingAddress']['Country'].'</td>';
                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                            $html .= '<td>License number</td>';
                                            $html .= '<td>Seller DEA License - '.$SenderInfo['dea_licence'].', Ship From <br />State License - '.$SenderInfo['state_licence'].'</td>';
                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                            $html .= '<td>ShippingNumber & Date</td>';
                                            $html .= '<td>'.$SenderInfo['shipping_number'].' '.$SenderInfo['shipping_date'].'</td>';
                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                            $html .= '<td>Transaction</td>';
                                            $html .= '<td>Sale</td>';
                                        $html .= '</tr>';
                                    $html .= '</table>';
                                $html .= '</td>';
                                $html .= '<td>';
                                    $html .= '<table class="seller-address-info">';
                                        $html .= '<tr>';
                                            $html .= '<th colspan="2">Recipient Information</th>';
                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                            $html .= '<td>Recipient<br />Business address</td>';
                                            $html .= '<td>'.$RecipientInfo['BusinessAddress']['BusinessName'].'<br />'.$RecipientInfo['BusinessAddress']['Street'].'<br />'.$RecipientInfo['BusinessAddress']['City'].', '.$RecipientInfo['BusinessAddress']['StateOrRegion'].', '.$RecipientInfo['BusinessAddress']['PostalCode'].', '.$RecipientInfo['BusinessAddress']['Country'].'</td>';
                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                            $html .= '<td>Shipping address</td>';
                                            $html .= '<td>'.$RecipientInfo['ShippingAddress']['BusinessName'].'<br />'.$RecipientInfo['ShippingAddress']['Street'].'<br />'.$RecipientInfo['ShippingAddress']['City'].', '.$RecipientInfo['ShippingAddress']['StateOrRegion'].', '.$RecipientInfo['ShippingAddress']['PostalCode'].', '.$RecipientInfo['ShippingAddress']['Country'].'</td>';
                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                            $html .= '<td>License number</td>';
                                            $html .= '<td>Ship To State License - '.$RecipientInfo['state_licence'].'</td>';
                                        $html .= '</tr>';
                                    $html .= '</table>';
                                $html .= '</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td colspan="2" class="drl-ship-footer">'.$shipmentPrintArray['AdditionalReferences'].'</td>';
                            $html .= '</tr>';
                        $html .= '</table>';
                        $html .= '</main>';

                    } else {
                        //echo '<pre>'.print_r($PedigreeXML->Pedigree->InitialPedigree, true).'</pre>';
                        $product_info = $PedigreeXML->Pedigree->InitialPedigree;
                        $TransactionInfo = $PedigreeXML->Pedigree->TransactionInfo;
                        $SenderInfo = $TransactionInfo->SenderInfo;
                        $RecipientInfo = $TransactionInfo->RecipientInfo;
                        $ndc_number = (array)$product_info->ProductInfo->ProductCode;
                        $dea_address = (array)$SenderInfo->BusinessAddress->AddressId;
                        $state_address = (array)$SenderInfo->ShippingAddress->AddressId;
                        $shipp_state_address = (array)$RecipientInfo->ShippingAddress->AddressId;
                        $AdditionalReferences = $PedigreeXML->Pedigree->AdditionalReferences->Ref1;
                        //echo '<pre>'.print_r($dea_address['$'], true).'</pre>';
                        //$product_infos = $block->getProductInfo($ndc_number['$']);
                        $ndc = $ndc_number['$'];
                        $sku = substr($ndc, 0, 5).'-'.substr($ndc, 5, 4).'-'.substr($ndc, 9);
                        $formated_sku = $sku;
                        $products[] = [
                            'product_mgf' => $product_info->ProductInfo->Manufacturer,
                            'product_name' => $product_info->ProductInfo->DrugName,
                            'product_sku' => $formated_sku,
                            'product_lot' => $product_info->ItemInfo->Lot,
                            'product_qty' => $product_info->ItemInfo->Quantity,
                            'product_expiry' => $product_info->ItemInfo->ExpirationDate
                        ];

                        $billing_address = [
                            'BusinessName' => $SenderInfo->BusinessAddress->BusinessName,
                            'Street' => $SenderInfo->BusinessAddress->Street1,
                            'City' => $SenderInfo->BusinessAddress->City,
                            'StateOrRegion' => $SenderInfo->BusinessAddress->StateOrRegion,
                            'PostalCode' => $SenderInfo->BusinessAddress->PostalCode,
                            'Country' => $SenderInfo->BusinessAddress->Country
                        ];
                        $shipping_address = [
                            'BusinessName' => $SenderInfo->ShippingAddress->BusinessName,
                            'Street' => $SenderInfo->ShippingAddress->Street1,
                            'City' => $SenderInfo->ShippingAddress->City,
                            'StateOrRegion' => $SenderInfo->ShippingAddress->StateOrRegion,
                            'PostalCode' => $SenderInfo->ShippingAddress->PostalCode,
                            'Country' => $SenderInfo->ShippingAddress->Country
                        ];

                        $rbilling_address = [
                            'BusinessName' => $RecipientInfo->BusinessAddress->BusinessName,
                            'Street' => $RecipientInfo->BusinessAddress->Street1,
                            'City' => $RecipientInfo->BusinessAddress->City,
                            'StateOrRegion' => $RecipientInfo->BusinessAddress->StateOrRegion,
                            'PostalCode' => $RecipientInfo->BusinessAddress->PostalCode,
                            'Country' => $RecipientInfo->BusinessAddress->Country,
                        ];
                        $rshipping_address = [
                            'BusinessName' => $RecipientInfo->ShippingAddress->BusinessName,
                            'Street' => $RecipientInfo->ShippingAddress->Street1,
                            'City' => $RecipientInfo->ShippingAddress->City,
                            'StateOrRegion' => $RecipientInfo->ShippingAddress->StateOrRegion,
                            'PostalCode' => $RecipientInfo->ShippingAddress->PostalCode,
                            'Country' => $RecipientInfo->ShippingAddress->Country,
                        ];

                        $shipmentPrintArray = [
                            'reference_number' => $TransactionInfo->TransactionIdentifier->Identifier,
                            'reference_date' => $TransactionInfo->TransactionDate,
                            'po_number' => $TransactionInfo->AltTransactionIdentifier->Identifier,
                            'products' => $products,
                            'seller_info' => [
                                'BusinessAddress' => $billing_address,
                                'ShippingAddress' => $shipping_address,
                                'dea_licence' => $dea_address['$'],
                                'state_licence' => $state_address['$'],
                                'shipping_number' => $TransactionInfo->TransactionIdentifier->Identifier,
                                'shipping_date' => $TransactionInfo->TransactionDate
                            ],
                            'recipient_info' => [
                                'BusinessAddress' => $rbilling_address,
                                'ShippingAddress' => $rshipping_address,
                                'state_licence' => $shipp_state_address['$']
                            ],
                            'AdditionalReferences' => $AdditionalReferences
                        ];
                        $html .= '<main class="page-main">';
                            $html .= '<table class="item-ref-information">';
                               $html .= '<tr>';
                                    $html .= '<th class="drl-ship-he-se">Reference No.</th>';
                                    $html .= '<th class="drl-ship-he-se">Reference date</th>';
                                    $html .= '<td class="drl-ship-na-se">Purchase Order Number</td>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td class="drl-ship-na-se">'.$shipmentPrintArray['reference_number'].'</td>';
                                    $html .= '<td class="drl-ship-na-se">'.$shipmentPrintArray['reference_date'].'</td>';
                                    $html .= '<td class="drl-ship-na-se">'.$shipmentPrintArray['po_number'].'</td>';
                                $html .= '</tr>';
                            $html .= '</table>';
                            $SenderInfo = $shipmentPrintArray['seller_info'];
                            $RecipientInfo = $shipmentPrintArray['recipient_info'];
                            $html .= '<p class="drl-ship-name">'.$SenderInfo['BusinessAddress']['BusinessName'].' sold the following items to '.$RecipientInfo['BusinessAddress']['BusinessName'].'</p>';
                            $html .= '<table class="item-information">';
                                $html .= '<tr>';
                                    $html .= '<th colspan="6" class="drl-ship-head">Product Information</th>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td class="drl-ship-he-se">Manufacture name</td>';
                                    $html .= '<td class="drl-ship-he-se">Drug name</td>';
                                    $html .= '<td class="drl-ship-he-se">NDC</td>';
                                    $html .= '<td class="drl-ship-he-se">Lot number</td>';
                                    $html .= '<td class="drl-ship-he-se">Quantity</td>';
                                    $html .= '<td class="drl-ship-he-se">Expiration date</td>';
                                $html .= '</tr>';
                            foreach($shipmentPrintArray['products'] as $product) {
                                $html .= '<tr>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_mgf'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_name'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_sku'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_lot'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_qty'].'</td>';
                                    $html .= '<td class="drl-ship-he-se">'.$product['product_expiry'].'</td>';
                                $html .= '</tr>';
                            }
                            $html .= '</table>';
                            $html .= '<p></p>';
                            $html .= '<table>';
                                $html .= '<tr>';
                                    $html .= '<th colspan="2" class="drl-ship-head">History of drug sales and distribution</th>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td width="60%">';
                                        $html .= '<table class="seller-address-info">';
                                            $html .= '<tr>';
                                                $html .= '<th colspan="2">Seller Information</th>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Seller<br />Business address</td>';
                                                $html .= '<td>'.$SenderInfo['BusinessAddress']['BusinessName'].'<br />'.$SenderInfo['BusinessAddress']['Street'].'<br />'.$SenderInfo['BusinessAddress']['City'].', '.$SenderInfo['BusinessAddress']['StateOrRegion'].', '.$SenderInfo['BusinessAddress']['PostalCode'].', '.$SenderInfo['BusinessAddress']['Country'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Shipping address</td>';
                                                $html .= '<td>'.$SenderInfo['ShippingAddress']['BusinessName'].'<br />'.$SenderInfo['ShippingAddress']['Street'].'<br />'.$SenderInfo['ShippingAddress']['City'].', '.$SenderInfo['ShippingAddress']['StateOrRegion'].', '.$SenderInfo['ShippingAddress']['PostalCode'].', '.$SenderInfo['ShippingAddress']['Country'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>License number</td>';
                                                $html .= '<td>Seller DEA License - '.$SenderInfo['dea_licence'].', Ship From <br />State License - '.$SenderInfo['state_licence'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>ShippingNumber & Date</td>';
                                                $html .= '<td>'.$SenderInfo['shipping_number'].' '.$SenderInfo['shipping_date'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Transaction</td>';
                                                $html .= '<td>Sale</td>';
                                            $html .= '</tr>';
                                        $html .= '</table>';
                                    $html .= '</td>';
                                    $html .= '<td>';
                                        $html .= '<table class="seller-address-info">';
                                            $html .= '<tr>';
                                                $html .= '<th colspan="2">Recipient Information</th>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Recipient<br />Business address</td>';
                                                $html .= '<td>'.$RecipientInfo['BusinessAddress']['BusinessName'].'<br />'.$RecipientInfo['BusinessAddress']['Street'].'<br />'.$RecipientInfo['BusinessAddress']['City'].', '.$RecipientInfo['BusinessAddress']['StateOrRegion'].', '.$RecipientInfo['BusinessAddress']['PostalCode'].', '.$RecipientInfo['BusinessAddress']['Country'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>Shipping address</td>';
                                                $html .= '<td>'.$RecipientInfo['ShippingAddress']['BusinessName'].'<br />'.$RecipientInfo['ShippingAddress']['Street'].'<br />'.$RecipientInfo['ShippingAddress']['City'].', '.$RecipientInfo['ShippingAddress']['StateOrRegion'].', '.$RecipientInfo['ShippingAddress']['PostalCode'].', '.$RecipientInfo['ShippingAddress']['Country'].'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td>License number</td>';
                                                $html .= '<td>Ship To State License - '.$RecipientInfo['state_licence'].'</td>';
                                            $html .= '</tr>';
                                        $html .= '</table>';
                                    $html .= '</td>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td colspan="2" class="drl-ship-footer">'.$shipmentPrintArray['AdditionalReferences'].'</td>';
                                $html .= '</tr>';
                            $html .= '</table>';
                        $html .= '</main>';
                    }
                }
            }
        }

        //echo $html; exit();

        $this->domoptions->setIsRemoteEnabled(true);
        $this->dompdf->setOptions($this->domoptions);
        $this->dompdf->setPaper('A4', 'landscape');
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
        //$this->dompdf->stream();

        $pdf_string = $this->dompdf->output();

        return base64_encode($pdf_string);
    }

    private function getStyle()
    {
        return '<style>
        p.drl-invocie-document {
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-weight: bold;
            font-size: 24px;
            font-family: "DRL Circular Bold";
        }
        table>thead>tr>th, table>tbody>tr>th, table>tfoot>tr>th, table>thead>tr>td, table>tbody>tr>td, table>tfoot>tr>td {
            padding: 11px 10px;
        }
        table>tbody>tr>th, table>tfoot>tr>th, table>tbody>tr>td, table>tfoot>tr>td {
            vertical-align: top;
        }
        th.drl-ship-head{
            border: 1px solid #5225B5;
            letter-spacing: 0px;
            color: #4F4F4F;
            text-transform: capitalize;
            opacity: 1;
            font-family: "DRL Circular Bold";
            border-right: 5px solid #5225B5;
            text-align: left;
            padding: 10px;
        }
        td.drl-ship-name{
                letter-spacing: 0px;
                color: #9D9AA2;
                opacity: 1;
                font-family: "DRL Circular Book";
                font-size: 14px;
        }
        td.drl-ship-sub-name{
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-family: "DRL Circular Bold";
            font-size: 16px;
            font-weight: bold;
        }
        p.drl-ship-name{
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-family: "DRL Circular Bold";
            font-size: 14px;
            margin-top: 1rem;
        }
        th.drl-ship-he-se{
            letter-spacing: 0px;
            color: #9D9AA2;
            opacity: 1;
            font-family: "DRL Circular Book";
            font-size: 14px;
            text-transform: capitalize;
        }
        td.drl-ship-na-se{
            letter-spacing: 0px;
            color: #434861;
            opacity: 1;
            font-family: "DRL Circular Book";
            font-size: 14px;
            text-transform: capitalize;
        }
        td.drl-ship-na-se,th.drl-ship-he-se{
            border: 1px solid #5225B5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            max-width: 100%;
        }
        table.seller-address-info tr th{
            letter-spacing: 0px;
            color: #4F4F4F;
            opacity: 1;
            font-family: "DRL Circular Bold";
            font-size: 16px;
            font-weight: bold;
        }
        table.seller-address-info tr td{
            letter-spacing: 0px;
            color: #434861;
            opacity: 1;
            font-family: "DRL Circular Book";
            font-size: 14px;
        }
        td.drl-ship-footer {
            letter-spacing: 0px;
            color: #434861;
            opacity: 1;
            font-family: "DRL Circular Light";
            font-size: 14px;
        }
        .page-main {
            /*box-sizing: border-box;
            margin-left: auto;
            margin-right: auto;
            max-width: 1280px;
            padding-left: 20px;
            padding-right: 20px;
            width: 100%;
            -webkit-flex-grow: 1;
            flex-grow: 1;
            display:block;*/
        }
        td.drl-ship-he-se {
            border: 1px solid #5225B5;
        }
        </style>';
    }
}
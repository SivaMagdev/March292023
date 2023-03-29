<?php
namespace Ecomm\Invoice\Block;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Element\Template\Context;
use PhpOffice\PhpSpreadsheet\SpreadsheetFactory;

class Export extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var SpreadsheetFactory
     */
    protected $phpSpreadsheet;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $_varDirectory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $invoiceCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    protected $attributeRepository;

    private $path = 'export';

    /**
     * Export constructor.
     *
     * @param Context $context
     * @param ResourceConnection $resourceConnection
     * @param SpreadsheetFactory $phpSpreadsheet
     * @param Filesystem $filesystem
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Eav\Model\AttributeRepository $attributeRepository
     * @param array $data
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ResourceConnection $resourceConnection,
        SpreadsheetFactory $phpSpreadsheet,
        Filesystem $filesystem,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->resourceConnection = $resourceConnection;
        $this->phpSpreadsheet = $phpSpreadsheet;
        $this->_varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->customerSession = $customerSession;
        $this->attributeRepository = $attributeRepository;
    }

    public function generateReport($isInvoice = false)
    {
        $records = [];
        $title = 'Test';
        if ($isInvoice) {
            $records = $this->getInvoiceCollection();
            $title = 'Invoice Report';
        } else {
            $records = $this->getOrderCollection();
            $title = 'Order Report';
        }
        $centerStyle =[
            'alignment'=> [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ];
        $spreadsheet = $this->getPhpSpreadSheet();
        $spreadsheet->getProperties()->setCreator('PhpOffice')
            ->setLastModifiedBy('PhpOffice')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('PhpOffice')
            ->setKeywords('PhpOffice')
            ->setCategory('PhpOffice');
        $spreadsheet->getActiveSheet()->setTitle($title);
        $spreadsheet->getActiveSheet()->fromArray(array_values($this->getFieldNameForExport()), null, 'A1');
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(45);
        $spreadsheet->getActiveSheet()->getStyle('A1:X1')->applyFromArray($centerStyle);
        $spreadsheet->getActiveSheet()->getStyle('A1:X1')->getAlignment()->setWrapText(true);
        $spreadsheet->getActiveSheet()->setAutoFilter('A1:X1');
        $currentRow = 2;
        foreach ($records as $rows) {
            $currentCol = 'A';
            foreach (array_keys($this->getFieldNameForExport()) as $key) {
                $value = (isset($rows[$key]) && $rows[$key] != '') ? $rows[$key] : "--";
                $spreadsheet->getActiveSheet()->setCellValue($currentCol . $currentRow, $value);
                $currentCol++;
            }
            unset($key);
            $currentRow++;
        }
        unset($rows);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $filePath = $this->getFilePath();
        $writer->save($filePath);
        return $filePath;
    }

    private function getInvoiceCollection()
    {
        $customerId = $this->customerSession->getCustomerData()->getId();
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select();
        $select->from(
                ['main_table' => 'sales_invoice'],
                ['order_id', 'shipping_address_id', 'increment_id', 'state', 'created_at']
            )->order('main_table.created_at DESC');
        $select->joinLeft(
            ['order' => 'sales_order'],
            'main_table.order_id = order.entity_id',
            ['customer_id', 'order_increment_id' => 'increment_id', 'discount_description', 'shipping_incl_tax', 'base_shipping_incl_tax', 'sap_id', 'batch_id']
        );
        $select->joinLeft(
            ['soi' => 'sales_order_item'],
            'order.entity_id = soi.order_id',
            [
                'product_name' => 'name',
                'ndc' => 'sku',
                'quantity' => 'qty_ordered',
                'sold' => 'qty_invoiced',
                'price' => 'base_price',
                'price_type',
                'discount_invoiced',
                'discount' => 'base_discount_invoiced',
                'invoice_total' => 'base_row_total'
            ]
        );
        $select->joinLeft(
            ['order_address' => 'sales_order_address'],
            'main_table.order_id = order_address.parent_id AND order_address.address_type="shipping"',
            ['customer_address_id']
        );
        $select->joinLeft(
            ['pod' => 'ecomm_sales_order_pod_ext'],
            'order.sap_id = pod.sales_order_id',
            ['delivery_date', 'delivery_time', 'po_number', 'po_date']
        );
        $select->joinLeft(
            ['customer' => 'customer_entity_varchar'],
            'order.customer_id = customer.entity_id AND customer.attribute_id IN (' . $this->getAttributeId('customer', 'dba') . ')',
            ['dba' => 'value']
        );
        $select->joinLeft(
            ['customer_address' => 'customer_address_entity'],
            'order_address.customer_address_id = customer_address.entity_id AND is_active=1',
            ['postcode']
        );
        $select->joinLeft(
            ['customer_address_int' => 'customer_address_entity_int'],
            'order_address.customer_address_id = customer_address_int.entity_id AND customer_address_int.attribute_id IN (' . $this->getAttributeId('customer_address', 'hin_status') . ')',
            ['hin_status' => 'value']
        );
        $attributes = [
            'dea_license_id' => $this->getAttributeId('customer_address', 'dea_license_id'),
            'three_four_b_id' => $this->getAttributeId('customer_address', 'three_four_b_id')];
        foreach ($attributes as $key => $attributeId) {
            $select->joinLeft(
                ['customer_address_varchar' . $key => 'customer_address_entity_varchar'],
                'order_address.customer_address_id = customer_address_varchar'. $key .'.entity_id AND customer_address_varchar'. $key .'.attribute_id IN (' . $attributeId . ')',
                [$key => 'value']
            );
        }
        unset($key, $attributeId);
        $select->where('order.customer_id = ?', $customerId);
        return $connection->fetchAll($select);
    }

    private function getOrderCollection()
    {
        $customerId = $this->customerSession->getCustomerData()->getId();
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select();
        $select->from(
            ['main_table' => 'sales_invoice'],
            ['customer_id', 'discount_amount', 'base_discount_amount', 'order_increment_id' => 'increment_id', 'discount_description', 'shipping_incl_tax', 'base_shipping_incl_tax', 'sap_id', 'batch_id']
        )->order('main_table.created_at DESC');
        $select->joinLeft(
            ['soi' => 'sales_order_item'],
            'main_table.entity_id = soi.order_id',
            ['product_name' => 'name', 'ndc' => 'sku', 'quantity' => 'qty_ordered', 'sold' => 'qty_invoiced', 'price' => 'base_price']
        );
        $select->joinLeft(
            ['order_address' => 'sales_order_address'],
            'main_table.order_id = order_address.parent_id AND order_address.address_type="shipping"',
            ['customer_address_id']
        );
        $select->joinLeft(
            ['pod' => 'ecomm_sales_order_pod_ext'],
            'main_table.sap_id = pod.sales_order_id',
            ['delivery_date', 'delivery_time', 'po_number', 'po_date']
        );
        $select->joinLeft(
            ['customer' => 'customer_entity_varchar'],
            'main_table.customer_id = customer.entity_id AND customer.attribute_id IN (' . $this->getAttributeId('customer', 'dba') . ')',
            ['dba' => 'value']
        );
        $select->joinLeft(
            ['customer_address' => 'customer_address_entity'],
            'order_address.customer_address_id = customer_address.entity_id AND is_active=1',
            ['postcode']
        );
        $select->joinLeft(
            ['customer_address_int' => 'customer_address_entity_int'],
            'order_address.customer_address_id = customer_address_int.entity_id AND customer_address_int.attribute_id IN (' . $this->getAttributeId('customer_address', 'hin_status') . ')',
            ['hin_status' => 'value']
        );
        $attributes = [
            'dea_license_id' => $this->getAttributeId('customer_address', 'dea_license_id'),
            'three_four_b_id' => $this->getAttributeId('customer_address', 'three_four_b_id')];
        foreach ($attributes as $key => $attributeId) {
            $select->joinLeft(
                ['customer_address_varchar' . $key => 'customer_address_entity_varchar'],
                'order_address.customer_address_id = customer_address_varchar'. $key .'.entity_id AND customer_address_varchar'. $key .'.attribute_id IN (' . $attributeId . ')',
                [$key => 'value']
            );
        }
        unset($key, $attributeId);
        $select->where('main_table.customer_id = ?', $customerId);
        return $connection->fetchAll($select);
    }

    private function getFieldNameForExport()
    {
        return [
            'order_increment_id' => 'Order #',
            'increment_id' => 'Invoice #',
            'sap_id' => 'Sales Order #',
            'state' => 'Invoice Status',
            'po_number' => 'Customer PO',
            'po_date' => 'Customer PO Date',
            'month' => 'Month-YR PO',
            'delivery_date' => 'Delivery Date',
            'dba' => 'Parent Health System',
            'pharmacy' => 'Pharmacy',
            'dea_license_id' => 'DEA Number',
            'three_four_b_id' => '340B #',
            'postcode' => 'Postal Code',
            'ndc' => 'NDC',
            'product_name' => 'Product',
            'quantity' => 'Quantity',
            'sold' => 'Eaches Sold',
            'lot' => 'Lot',
            'expiry_date' => 'Exp Date',
            'price' => 'Pack Price $',
            'price_type' => 'Price Type (Direct/WAC/PHS)',
            'discount' => 'Discount (If Applicable)',
            'base_shipping_incl_tax' => 'Shipping',
            'invoice_total' => 'Invoice Value/Net Sale AMT'
        ];
    }

    /**
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    protected function getPhpSpreadSheet()
    {
        return $this->phpSpreadsheet->create();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getFilePath()
    {
        $filename = 'invoice_report.xlsx';
        $this->_varDirectory->create($this->path);
        $fullPath = $this->_varDirectory->getAbsolutePath($this->path . '/' . $filename);
        return $fullPath;
    }

    private function getAttributeId($type, $name)
    {
        /*$attributeNames = [
            'customer' => ['dba'],
            'customer_address' => ['three_four_b_id', 'hin_status'],
        ]*/
        return $this->attributeRepository->get($type, $name)->getAttributeId();
    }
}
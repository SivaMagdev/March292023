<?php
/**
 * @category   Ecomm
 * @package    Ecomm_Invoice
 * @author     harendra.kumar@pwc.com
 */

namespace Ecomm\Invoice\Block;

class Invoice extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_orderCollectionFactory;

    protected $customerSession;

    protected $_orderFactory;

    protected $invoices;

    protected $_date;

    protected $invoiceCollectionFactory;
    protected $request;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory, \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory, \Magento\Framework\Stdlib\DateTime\DateTime $date, \Magento\Framework\App\Request\Http $request, \Magento\Sales\Model\OrderFactory $orderFactory, \Magento\Framework\Message\ManagerInterface $messageManager, array $data = [])
    {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->_date = $date;
        $this->request = $request;
        $this->messageManager = $messageManager;
        parent::__construct($context, $data);
    }
    public function getOrderCollection()
    {
        $customerId = $this
            ->customerSession
            ->getCustomer()
            ->getId();
        $collection = $this
            ->_orderCollectionFactory
            ->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('customer_id', $customerId)->setOrder('created_at', 'desc')
            ->setPageSize(5);
        return $collection;
    }
    public function getLoggedinCustomerName()
    {
        $customerId = $this
            ->customerSession
            ->getCustomer()
            ->getName();
        return $customerId;
    }
    public function getOrderStatus()
    {
        $today_date = $this
            ->_date
            ->date('Y-m-d');
        $status = ['out_for_delivery', 'complete'];
        $customerId = $this
            ->customerSession
            ->getCustomer()
            ->getId();
        $orderstatus = $this
            ->_orderFactory
            ->create()
            ->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customerId)->addFieldToFilter('status', ['in' => $status])->addFieldToFilter('rgdd_delivery_date', array(
            'gteq' => $today_date
        ))->setOrder('created_at', 'desc');
        return $orderstatus;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    public function _prepareLayout()
    {

        if ($this->getInvoiceList())
        {
            $pager = $this->getLayout()
                ->createBlock('Magento\Theme\Block\Html\Pager', 'ecomm.invoice.pager')
                ->setAvailableLimit(array(
                5 => 5,
                10 => 10,
                15 => 15,
                25 => 25,
                40 => 40
            ))
                ->setShowPerPage(true)
                ->setCollection($this->getInvoiceList());
            $this->setChild('pager', $pager);

            $pager = $this->getLayout()
                ->createBlock('Magento\Theme\Block\Html\Pager', 'ecomm.invoice.pagers')
                ->setAvailableLimit(array(
                5 => 5,
                10 => 10,
                15 => 15,
                25 => 25,
                40 => 40
            ))
                ->setShowPerPage(true)
                ->setCollection($this->getOrderFilter());
            $this->setChild('pager', $pager);
            
        }

        return parent::_prepareLayout();
    }

    public function getInvoiceList()
    {

        $customerId = $this
            ->customerSession
            ->getCustomer()
            ->getId();
        $orders = $this
            ->_orderCollectionFactory
            ->create()
            ->addfieldToFilter('customer_id', $customerId)->addFieldToSelect('*');
        $orderids = $orders->getColumnValues('entity_id');
        if (count($orderids))
        {
            $page = ($this->getRequest()
                ->getParam('p')) ? $this->getRequest()
                ->getParam('p') : 1;
            $pageSize = ($this->getRequest()
                ->getParam('limit')) ? $this->getRequest()
                ->getParam('limit') : 5;
            $this->invoices = $this
                ->invoiceCollectionFactory
                ->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', ['in' => $orderids])->setOrder('created_at', 'desc')
                ->setPageSize($pageSize)->setCurPage($page);
        }
        return $this->invoices;
    }
    public function getInvoiceListOrder()
    {

        $customerId = $this
            ->customerSession
            ->getCustomer()
            ->getId();
        $orders = $this
            ->_orderCollectionFactory
            ->create()
            ->addfieldToFilter('customer_id', $customerId)->addFieldToSelect('*');
        $orderids = $orders->getColumnValues('entity_id');
        if (count($orderids))
        {
            $this->invoiceslist = $this
                ->invoiceCollectionFactory
                ->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', ['in' => $orderids])->setOrder('created_at', 'desc');
        }
        return $this->invoiceslist;
    }

    public function getOrderFilter()
    {

        $this
            ->request
            ->getParams();

        if (!empty($this
            ->request
            ->getParam('order-number')))
        {

            $data = $this->getInvoiceListOrder();
            $items = array();
            foreach ($data as $invoiceList)
            {
                $items[] = $invoiceList->getOrder()
                    ->getIncrementId();

            }
            $order = $this
                ->request
                ->getParam('order-number');
            if (in_array($order, $items))
            {
                $page = ($this->getRequest()
                    ->getParam('p')) ? $this->getRequest()
                    ->getParam('p') : 1;
                $pageSize = ($this->getRequest()
                    ->getParam('limit')) ? $this->getRequest()
                    ->getParam('limit') : 5;

                $this->invoices = $this
                    ->invoiceCollectionFactory
                    ->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('order_id', $this
                    ->request
                    ->getParam('order-number'))
                    ->setOrder('created_at', 'desc')
                    ->setPageSize($pageSize)->setCurPage($page);
            }
            else
            {
                $this
                    ->messageManager
                    ->addWarning(__("Please Use Valid Order Id........"));
            }
        }

        elseif (!empty($this
            ->request
            ->getParam('invoice-number')))
        {
            $data = $this->getInvoiceListOrder();
            $invoiceid = array();
            foreach ($data as $invoiceList)
            {
                $invoiceid[] = $invoiceList->getIncrementId();

            }
            $invoice = $this
                ->request
                ->getParam('invoice-number');
            if (in_array($invoice, $invoiceid))
            {

                $page = ($this->getRequest()
                    ->getParam('p')) ? $this->getRequest()
                    ->getParam('p') : 1;
                $pageSize = ($this->getRequest()
                    ->getParam('limit')) ? $this->getRequest()
                    ->getParam('limit') : 5;
                $this->invoices = $this
                    ->invoiceCollectionFactory
                    ->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('increment_id', ['in' => $this
                    ->request
                    ->getParam('invoice-number') ])
                    ->setOrder('created_at', 'desc')
                    ->setPageSize($pageSize)->setCurPage($page);
            }
            else
            {
                $this
                    ->messageManager
                    ->addWarning(__("Please Use  valid Invoice........"));
            }
        }
        elseif (!empty($this
            ->request
            ->getParam('invoice-status')))
        {
            $data = $this->getInvoiceListOrder();
            $status = array();
            $invoiceid = array();
            foreach ($data as $invoiceList)
            {
                $status[] = $invoiceList->getState();
                $invoiceid[] = $invoiceList->getIncrementId();

            }
            // print_r
            $statudataa = $this
                ->request
                ->getParam('invoice-status');

            $page = ($this->getRequest()
                ->getParam('p')) ? $this->getRequest()
                ->getParam('p') : 1;

            $pageSize = ($this->getRequest()
                ->getParam('limit')) ? $this->getRequest()
                ->getParam('limit') : 5;
            $this->invoices = $this
                ->invoiceCollectionFactory
                ->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('increment_id', ['in' => $invoiceid])->addFieldToFilter('state', ['in' => $statudataa])->setOrder('created_at', 'desc')
                ->setPageSize($pageSize)->setCurPage($page);

        }

        elseif (!empty($this
            ->request
            ->getParam('Sap-Number')))
        {
            $data = $this->getInvoiceListOrder();
            $sapId = array();
            foreach ($data as $invoiceList)
            {
                $sapId[] = $invoiceList->getOrder()
                    ->getSapId();

            }
            $sap = $this
                ->request
                ->getParam('Sap-Number');
            if (in_array($sap, $sapId))
            {

                $page = ($this->getRequest()
                    ->getParam('p')) ? $this->getRequest()
                    ->getParam('p') : 1;
                $pageSize = ($this->getRequest()
                    ->getParam('limit')) ? $this->getRequest()
                    ->getParam('limit') : 5;
                $order_sap = $this->invoices = $this
                    ->_orderFactory
                    ->create()
                    ->getCollection()
                    ->addFieldToSelect('*')
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('sap_id', ['in' => $this
                    ->request
                    ->getParam('Sap-Number') ])
                    ->setOrder('created_at', 'desc');
                foreach ($order_sap as $sap)
                {
                    $sapid = $sap->getIncrementId();
                }
                if (!empty($sapid))
                {
                    $page = ($this->getRequest()
                        ->getParam('p')) ? $this->getRequest()
                        ->getParam('p') : 1;
                    $pageSize = ($this->getRequest()
                        ->getParam('limit')) ? $this->getRequest()
                        ->getParam('limit') : 5;

                    $this->invoices = $this
                        ->invoiceCollectionFactory
                        ->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('order_id', ['in' => $sapid])->setOrder('created_at', 'desc')
                        ->setPageSize($pageSize)->setCurPage($page);
                }

            }
            else
            {
                $this
                    ->messageManager
                    ->addWarning(__("Please Use Valid SAP Number........"));
            }
        }

        return $this->invoices;
    }
    public function getPrintInvoiceUrl($invoice)
    {
        return $this->getUrl('sales/order/printInvoice', ['invoice_id' => $invoice->getId() ]);
    }
}


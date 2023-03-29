<?php

namespace Ecomm\PriceEngine\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Export controller class
 */
class ExportAnniversary extends \Magento\Backend\App\Action
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Constructor
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param CustomerFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->fileFactory = $fileFactory;
        $this->customerFactory      = $customerFactory;
        $this->customerRepository      = $customerRepository;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $customerIds = $this->getRequest()->getParam('selected');

        if (!is_array($customerIds) || empty($customerIds)) {
            $customerIds = $this->customerSearch($this->getRequest()->getParam('search'));
        }

        if (!is_array($customerIds) || empty($customerIds)) {
            $this->messageManager->addErrorMessage(__('Please select Customer(s).'));
        } else {
            try {
                $name = 'customer-firstlogin-report-'.date('m-d-Y-H-i-s');
                $filepath = 'export/' .$name. '.csv';
                $this->directory->create('export');

                $stream = $this->directory->openFile($filepath, 'w+');
                $stream->lock();
                $columns = ['Customer Name','Email','First Login'];

                foreach ($columns as $column) {
                    $header[] = $column;
                }
                $stream->writeCsv($header);

                foreach ($customerIds as $customer_id) {

                    $itemData = [];

                    $customer= $this->customerRepository->getById($customer_id);

                    $inc = 0;

                    $itemData[$inc] = $customer->getFirstName() . ' ' . $customer->getLastName(); $inc++;
                    $itemData[$inc] = $customer->getEmail(); $inc++;
                    if (!empty($customer->getCustomAttribute("first_login"))) {
                        $itemData[$inc] = $customer->getCustomAttribute("first_login")->getValue();
                    } else {
                        $itemData[$inc] = '';
                    }
                    $inc++;

                    $stream->writeCsv($itemData);
                }

                //exit();

                $content = [];
                $content['type'] = 'filename'; // must keep filename
                $content['value'] = $filepath;
                $content['rm'] = '1'; //remove csv from var folder

                $csvfilename = $name.'.csv';
                return $this->fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

    }

    private function customerSearch($search_keyword) {
        $customerIds = [];
        $collection = $this->customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addAttributeToFilter(
                        array(
                            array('attribute' => 'firstname', 'like' => '%' . $search_keyword . '%'),
                            array('attribute' => 'lastname', 'like' => '%' . $search_keyword . '%'),
                            array('attribute' => 'email', 'like' => '%' . $search_keyword . '%')
                        ))->load();
        $customers = $collection->getData();
        foreach($customers as $customer){
            $customerIds[] = $customer['entity_id'];
        }

        return $customerIds;
    }
}

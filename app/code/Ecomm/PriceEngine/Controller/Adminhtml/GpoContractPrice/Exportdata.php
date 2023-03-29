<?php
namespace Ecomm\PriceEngine\Controller\Adminhtml\GpoContractPrice;

use Magento\Framework\App\Filesystem\DirectoryList;

class Exportdata extends \Magento\Backend\App\Action
{

    protected $uploaderFactory;

    protected $loggedCountFactory;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\App\Response\Http\FileFactory $fileFactory, \Magento\Framework\Filesystem $filesystem, \Ecomm\PriceEngine\Model\ContractPriceFactory $contractPriceFactory
)
    {
        parent::__construct($context);
        $this->_fileFactory = $fileFactory;
        $this->contractPriceFactory = $contractPriceFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
        parent::__construct($context);
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
     
          $contractPriceFactory = $this
                ->contractPriceFactory
                ->create()
                ->getCollection()->distinct(true);
            $itemDataall = array();
            foreach ($contractPriceFactory as $item)
            {
                $itemsku = $item->getData('sku');
                $productObj = $productRepository->get($itemsku);

                $product_name = $productObj->getName();

                $contractPriceFactory = $this->contractPriceFactory->create()->getCollection()->addFieldToFilter('sku', ['in' => $itemsku]);
                $item = array();
                $sku = array();
                $mat = array();
                $itemsname = array();
                $cid = array();
        
                foreach ($contractPriceFactory as $key)
                {

                    $item[] = $key['sku'];
                    $mat[] = $key['material'];
                    $price[] = $key['price'];
                    $cid[] = $key['contract_id'];
                    $itemsname[] = $product_name;

                }
               
                $item = array_unique($item);
                $mat = array_unique($mat);
                $material_id = $mat[0];
                $itemsname = array_unique($itemsname);
                $items = array_merge($item, $mat, $itemsname);
                $itemsid=array_unique($items);

                $valueCid = array();
                foreach ($cid as $value)
                {
                    $valueCid[] = $value;

                }

                if (is_numeric(array_search("3000000351", $valueCid)))
                {
                    $contractPriceFactorypr = $this->contractPriceFactory->create()->getCollection()->addFieldToFilter('contract_id', ['in' => '3000000351'])->addFieldToFilter('material', ['in' => $material_id]);

                    foreach ($contractPriceFactorypr as $price)
                    {

                        $items[] = $price['price'];
                    }

                }
                else
                {
                    $items[] = "";

                }
                if (is_numeric(array_search("3000000352", $valueCid)))
                {
                   $contractPriceFactorypr = $this->contractPriceFactory->create()->getCollection()->addFieldToFilter('contract_id', ['in' => '3000000352'])->addFieldToFilter('material', ['in' => $material_id]);

                    foreach ($contractPriceFactorypr as $price)
                    {

                        $items[] = $price['price'];
                    }

                }
                else
                {
                    $items[] = "";

                }

                if (is_numeric(array_search("3000001343", $valueCid)))
                {

                     $contractPriceFactorypr = $this->contractPriceFactory->create()->getCollection()->addFieldToFilter('contract_id', ['in' => '3000001343'])->addFieldToFilter('material', ['in' => $material_id]);
                    foreach ($contractPriceFactorypr as $price)
                    {

                        $items[] = $price['price'];
                    }
                }
                else
                {
                    $items[] = "";

                }
                if (is_numeric(array_search("3000001541", $valueCid)))
                {

                   $contractPriceFactorypr = $this->contractPriceFactory->create()->getCollection()->addFieldToFilter('contract_id', ['in' => '3000001541'])->addFieldToFilter('material', ['in' => $material_id]);
                    foreach ($contractPriceFactorypr as $price)
                    {

                        $items[] = $price['price'];
                    }

                }
                else
                {
                    $items[] = "";

                }

                if (is_numeric(array_search("3000000911", $valueCid)))
                {

                    $contractPriceFactorypr = $this->contractPriceFactory->create()->getCollection()->addFieldToFilter('contract_id', ['in' => '3000000911'])->addFieldToFilter('material', ['in' => $material_id]);
                    foreach ($contractPriceFactorypr as $price)
                    {

                        $items[] = $price['price'];
                    }

                }
                else
                {
                    $items[] = "";

                }

                if (is_numeric(array_search("3000000912", $valueCid)))
                {

                   $contractPriceFactorypr = $this->contractPriceFactory->create()->getCollection()->addFieldToFilter('contract_id', ['in' => '3000000912'])->addFieldToFilter('material', ['in' => $material_id]);
                    foreach ($contractPriceFactorypr as $price)
                    {

                        $items[] = $price['price'];
                    }

                }
                else
                {
                    $items[] = "";

                }

                if (is_numeric(array_search("3000000440", $valueCid)))
                {

                   $contractPriceFactorypr = $this->contractPriceFactory->create()->getCollection()->addFieldToFilter('contract_id', ['in' => '3000000440'])->addFieldToFilter('material', ['in' => $material_id]);
        
                    foreach ($contractPriceFactorypr as $price)
                    {

                        $items[] = $price['price'];
                    }

                }
                else
                {
                    $items[] = "";

                }
                if (is_numeric(array_search("3000000441", $valueCid)))
                {

                    $contractPriceFactorypr = $this->contractPriceFactory->create()->getCollection()->addFieldToFilter('contract_id', ['in' => '3000000441'])->addFieldToFilter('material', ['in' => $material_id]);
                    foreach ($contractPriceFactorypr as $price)
                    {

                        $items[] = $price['price'];
                    }

                }
                else
                {
                    $items[] = "";

                }

                if (is_numeric(array_search("1000001040", $valueCid)))
                {

                    $contractPriceFactorypr = $this->contractPriceFactory->create()->getCollection()->addFieldToFilter('contract_id', ['in' => '1000001040'])->addFieldToFilter('material', ['in' => $material_id]);
                    foreach ($contractPriceFactorypr as $price)
                    {

                        $items[] = $price['price'];
                    }

                }
                else
                {
                    $items[] = "";

                }
                if (is_numeric(array_search("1000001042", $valueCid)))
                {

                    $contractPriceFactorypr = $this->contractPriceFactory->create()->getCollection()->addFieldToFilter('contract_id', ['in' => '1000001042'])->addFieldToFilter('material', ['in' => $material_id]);
                    foreach ($contractPriceFactorypr as $price)
                    {

                        $items[] = $price['price'];
                    }

                }
                else
                {
                    $items[] = "";

                }

                if (is_numeric(array_search("1000001041", $valueCid)))
                {

                    $contractPriceFactorypr = $this->contractPriceFactory->create()->getCollection()->addFieldToFilter('contract_id', ['in' => '1000001041'])->addFieldToFilter('material', ['in' => $material_id]);
                    foreach ($contractPriceFactorypr as $price)
                    {

                        $items[] = $price['price'];
                    }

                }
                else
                {
                    $items[] = "";

                }

                if (is_numeric(array_search("1000001043", $valueCid)))
                {

                     $contractPriceFactorypr = $this->contractPriceFactory->create()->getCollection()->addFieldToFilter('contract_id', ['in' => '1000001043'])->addFieldToFilter('material', ['in' => $material_id]);
                    foreach ($contractPriceFactorypr as $price)
                    {

                        $items[] = $price['price'];
                    }

                }
                else
                {
                    $items[] = "";

                }

                $product_ihs = $productObj->getPhsIndirect();
                $product_sub_wac = $productObj->getSubWac();
                $items[] = $product_ihs;
                $items[] = $product_sub_wac;

                $itemDataall[] = $items;

            }

           header('Content-Type: text/csv');
          header('Content-Disposition: attachment; filename="GpoContractPrice.csv"');
            $out = fopen("php://output", 'w');
            fputcsv($out, array(
                'Product SKU',
                'SAP Material ID',
                'Product Name',
                'Vizient Base(3000000351)',
                'Vizient DSH (3000000352)',
                'Vizient Base (3000001343)',
                'Vizient DSH (3000001541)',
                'Premier Base (3000000911)',
                ' Premier DSH (3000000912)',
                'HealthTrust Base (3000000440)',
                'HealthTrust DSH ( 3000000441)',
                'RCA - Vizient (1000001040)',
                'RCA - Premier (1000001042)',
                'RCA - HealthTrust (1000001041)',
                'RCA - Others (1000001043)',
                'PHS Indirect Wac',
                'Sub-Wac'
            ));
            foreach ($itemDataall as $data)

            {
                fputcsv($out, $data);

            }
            fclose($out);

        }

    }

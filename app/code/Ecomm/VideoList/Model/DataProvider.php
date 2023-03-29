<?php
/**
 * PwC India
 *
 * @category Magento
 * @package BekaertSWSb2B_RequestCertificate
 * @author PwC India
 * @license GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Ecomm\VideoList\Model;
 
use Ecomm\VideoList\Model\ResourceModel\VideoList\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $_loadedData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $employeeCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $employeeCollectionFactory,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $employeeCollectionFactory->create();
        // $this->banner = $banner;
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Return Video List
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $employee) {
             $itemData = $employee->getData();
             if (isset($itemData['image'])) {
                $imageUrl = $itemData['image'];
                unset($itemData['image']);
                $itemData['image'][0] = [
                    'name' => $itemData['image_name'],
                    'url' => $imageUrl
                ];
            }

            $this->_loadedData[$employee->getId()] = $itemData;
        }

        return $this->_loadedData;
    }
}

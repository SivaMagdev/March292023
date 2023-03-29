<?php

namespace Ecomm\Servicerequest\Model;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Ecomm\Servicerequest\Model\ResourceModel\Servicerequest\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class ServicerequestProvider extends AbstractDataProvider
{
    /**
     * @var ResourceModel\Servicerequest\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $pageCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $pageCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection    = $pageCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        $this->meta           = $this->prepareMeta($this->meta);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $page) {
            $_data = $page->getData();
            $page->load($page->getId());

            if (isset($_data['attachment'])) {
                $image = [];
                $image[0]['name'] = $page->getAttachment();
                $image[0]['url'] = $this->storeManager
                ->getStore()
                ->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).'servicerequest/tmp/attachment/'.$page->getAttachment();
                $_data['attachment'] = $image;
            }

            if (isset($_data['solution_attachment'])) {
                $image = [];
                $image[0]['name'] = $page->getSolutionAttachment();
                $image[0]['url'] = $this->storeManager
                ->getStore()
                ->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).'servicerequest/tmp/attachment/'.$page->getSolutionAttachment();
                $_data['solution_attachment'] = $image;
            }
            $page->setData($_data);
            $this->loadedData[$page->getId()] = $page->getData();
        }
        $data = $this->dataPersistor->get('module_messages');
        if (!empty($data)) {
            $page = $this->collection->getNewEmptyItem();
            $page->setData($data);
            $this->loadedData[$page->getId()] = $page->getData();
            $this->dataPersistor->clear('module_messages');
        }
        return $this->loadedData;
    }
}

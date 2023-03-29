<?php

/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_HinValidator
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\HinValidator\Block\Adminhtml\Index;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Glob;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Ecomm\HinValidator\Model\Request;
use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Index block class
 */
class Runner extends \Magento\Backend\Block\Template
{
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var CollectionFactory
     */
    protected $addressCollectionFactory;

    /**
     * @var Glob
     */
    protected $glob;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param StoreRepositoryInterface $storeRepository
     * @param CollectionFactory $addressCollectionFactory
     * @param Glob $glob
     * @param Request $request
     * @param AddressRepositoryInterface $addressRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreRepositoryInterface $storeRepository,
        CollectionFactory $addressCollectionFactory,
        Glob $glob,
        Request $request,
        AddressRepositoryInterface $addressRepository,
        array $data = []
    ) {
        $this->storeRepository = $storeRepository;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->glob = $glob;
        $this->request = $request;
        $this->addressRepository = $addressRepository;
        parent::__construct($context, $data);
    }

    /**
     * To get store list
     *
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    public function storelist()
    {
        return $this->storeRepository->getList();
    }

    /**
     * To Run HIN Validation
     *
     * @return \Magento\Store\Model\ResourceModel\Website\Collection
     */
    public function getHinValidation()
    {
        $result = [];
        $collection = $this->addressCollectionFactory->create()
        ->addFieldToFilter('hin_id', ['neq' => 'NULL','neq' => ''])
        ->addFieldToFilter('sap_address_code', ['neq' => 'NULL','neq' => '']);

        $result['count'] =  $collection->getSize();
        if ($collection->getSize() > 0) {
            $dataCollection = [];
           
            foreach ($collection as $address) {
                array_push($dataCollection, ['HIN'=>$address->getData('hin_id'),
                                              'ShipToId'=>$address->getData('sap_address_code')]);
            }
            $webData = json_decode($this->request->push(['data'=> $dataCollection]), true);
            $result['status'] = 'Active';
            
            if ($webData != '' && $webData != null && isset($webData['data'])) {
              
                $result['data'] =  $this->setHimStatus($collection, $webData);
            } else {
                $result['status'] = 'Error';
                $result['data'] = null;
            }
            
        }
        return $result;
    }

    /**
     * To Run HIM Validation
     *
     * @param array $addressData
     * @param Json $webData
     * @return \Magento\Store\Model\ResourceModel\Website\Collection
     */
    public function setHimStatus($addressData, $webData)
    {
        $output= [];
        foreach ($addressData as $pro) {
            $data = [];
            $data['id'] = $pro->getData('entity_id');
            $data['hin_id'] = $pro->getData('hin_id');
            $data['status'] = 'Faild';
            foreach ($webData['data'] as $coll) {

                if ($coll['HIN'] == $pro->getData('hin_id') &&
                 $coll['ShipToId'] == $pro->getData('sap_address_code')) {
                    try {
                        $address = $this->addressRepository->getById($pro->getData('entity_id'));
                        $status = false;
                        if ($coll['ELIGIBILITY'] == 'Yes') {
                            $status = true;
                        } elseif ($coll['ELIGIBILITY'] == 'No') {
                            $status = false;
                        }

                        $address->setCustomAttribute('hin_status', $status);
                        $this->addressRepository->save($address);
                        $data['status'] = 'Success';
                    } catch (Exception $err) {
                        $data['status'] = 'Faild';
                    }
                }
                
            }
            array_push($output, $data);
        }
        return $output;
    }
}

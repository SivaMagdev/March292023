<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ecomm\Register\Controller\Address;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Stdlib\DateTime;

class Edit extends \Magento\Customer\Controller\Address implements HttpGetActionInterface
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ecomm\Notification\Helper\Data $notificationData,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    ) {
        $this->_addressRepository = $addressRepository;
        $this->notificationData = $notificationData;
        parent::__construct(
            $context,
            $customerSession,
            $formKeyValidator,
            $formFactory,
            $addressRepository,
            $addressDataFactory,
            $regionDataFactory,
            $dataProcessor,
            $dataObjectHelper,
            $resultForwardFactory,
            $resultPageFactory
        );
    }


    /**
     * Customer address edit action
     *
     * @return \Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        if ($addressId = $this->getRequest()->getParam('id')) {
            try {
                $_address = $this->_addressRepository->getById($addressId);
            } catch (NoSuchEntityException $e) {
                $_address = null;
            }
        }

        // state license expiry logic
        $diff_state_status = false;
        if(!empty($_address->getCustomAttribute("state_license_expiry"))){
            //Convert them to timestamps.
            $state_license_expiry = date_create($_address->getCustomAttribute("state_license_expiry")->getValue());
            $now = date("Y-m-d H:i:s", time());
            $date_now = date_create($now);
            $diff = date_diff($state_license_expiry, $date_now);

            //Calculate the difference.
            if($diff->invert && $diff->days >  $this->notificationData->getSlThreshold())
            {
                $diff_state_status = true;
            }
        }

        // DEA license expiry logic
        $diff_dea_status = false;
        if(!empty($_address->getCustomAttribute("dea_license_expiry"))){
            $dea_license_expiry = date_create($_address->getCustomAttribute("dea_license_expiry")->getValue());
            $now = date("Y-m-d H:i:s", time());
            $date_now = date_create($now);
            $diff_dea = date_diff($dea_license_expiry, $date_now);

            if($diff_dea->invert && $diff_dea->days > $this->notificationData->getDeaThreshold()){
                $diff_dea_status = true;
            }                             
        }

        if($diff_state_status && $diff_dea_status){
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/index');
            return $resultRedirect;
        }


        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('form');
    }
}

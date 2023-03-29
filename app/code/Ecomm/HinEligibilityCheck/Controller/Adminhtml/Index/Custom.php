<?php

namespace Ecomm\HinEligibilityCheck\Controller\Adminhtml\Index;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Custom extends \Magento\Backend\App\Action
{

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    public function __construct(
        AddressRepositoryInterface $addressRepository
    ) {
        $this->addressRepository = $addressRepository;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($addressId = $this->getRequest()->getParam('address_id')) {
            try {

                // get customer
                $addressInfo = $this->addressRepository->getById($addressId);

                print_r($addressInfo->getData());
                die;

                // // do something here
                // // put your code here

                // // add message
                // $this->messageManager->addSuccess(__('You have done this.'));

                // // redirect
                // $resultRedirect->setPath('customer/index/edit', ['id' => $customerId, '_current' => true]);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $resultRedirect->setPath('customer/index/edit', ['id' => $customerId, '_current' => true]);
            }
        } else {
            $this->messageManager->addError(__('We can\'t find a customer to perform this action'));
            $resultRedirect->setPath('customer/index/index');
        }
        return $resultRedirect;
    }
}
<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecomm\HinEligibilityCheck\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Index implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param RedirectFactory $resultRedirectFactory
     * @param RequestInterface $request
     * @param Curl $curl
     * @param Json $json
     * @param ResultFactory $resultFactory
     * @param ManagerInterface $messageManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        PageFactory $resultPageFactory, 
        AddressRepositoryInterface $addressRepository, 
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory, 
        \Magento\Framework\App\RequestInterface $request,
        Curl $curl,
        Json $json,
        ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig
    ){
        $this->resultPageFactory = $resultPageFactory;
        $this->addressRepository = $addressRepository;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->request = $request;
        $this->curl = $curl;
        $this->json = $json;
        $this->resultFactory = $resultFactory;
        $this->_messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($addressId = $this->request->getParam('address_id')) {
            try {

                // get customer address
                $addressInfo = $this->addressRepository->getById($addressId);
                $customerId = $addressInfo->getCustomerId();
                $hinId = '';

                if ($addressInfo->getCustomAttribute('sap_address_code')) {
                    $sapAddressId = $addressInfo->getCustomAttribute('sap_address_code')->getValue();
                } else {
                    $addressInfo->setCustomAttribute('hin_status', '0');
                    $this->addressRepository->save($addressInfo);
                    $resultRedirect->setPath('customer/index/edit', ['id' => $customerId, '_current' => true]);
                    $this->_messageManager->addError("SAP address ID is missing for this address. Please check and try again");
                    return $resultRedirect;
                }

                if ($addressInfo->getCustomAttribute('hin_id')) {
                    $hinId = $addressInfo->getCustomAttribute('hin_id')->getValue();
                } else {
                    $addressInfo->setCustomAttribute('hin_status', '0');
                    $this->addressRepository->save($addressInfo);
                    $resultRedirect->setPath('customer/index/edit', ['id' => $customerId, '_current' => true]);
                    $this->_messageManager->addError("HIN number is missing for this address. Please check and try again");
                    return $resultRedirect;
                }

                $addressData['data'][] = [
                    "HIN" => $hinId,
                    "ShipToId" => $sapAddressId,
                ];

                $username = $this->getUsername();
                $password = $this->getPassword();

                $this->curl->setOption(CURLOPT_USERPWD, $username . ":" . $password);
                $this->curl->setOption(CURLOPT_HEADER, 0);
                $this->curl->setOption(CURLOPT_TIMEOUT, 60);
                $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
                $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'GET');
                $this->curl->addHeader("Content-Type", "application/json");
                $url = $this->getHinUrl();
                $this->curl->post($url, $this->json->serialize($addressData));

                $response =  json_decode($this->curl->getBody(), true);

                $memberId = '';
                $threeFourtyId = '';
                $threeFourtyStart = '';
                $threeFourtyEnd = '';
                $hinStart = '';
                $hinEnd = '';
                
                if (isset($response['data'][0]['MemberID'])) {
                        $memberId = $response['data'][0]['MemberID'];
                }

                if (isset($response['data'][0]['ID'])) {
                        $threeFourtyId = $response['data'][0]['ID'];
                }

                if (isset($response['data'][0]['stdt']) && $response['data'][0]['stdt'] > 0) {
                        $threeFourtyStart = $response['data'][0]['stdt'];
                } else {
                    $threeFourtyStart = '';
                }

                if (isset($response['data'][0]['enddt']) && $response['data'][0]['enddt'] > 0) {
                        $threeFourtyEnd = $response['data'][0]['enddt'];
                } else {
                        $threeFourtyEnd = '';
                }

                if (isset($response['data'][0]['HINstdt']) && $response['data'][0]['HINstdt'] > 0) {
                        $hinStart = $response['data'][0]['HINstdt'];
                } else {
                        $hinStart = '';
                }

                if (isset($response['data'][0]['HINenddt']) && $response['data'][0]['HINenddt'] > 0) {
                        $hinEnd = $response['data'][0]['HINenddt'];
                } else {
                        $hinEnd = '';
                }


                if (isset($response['data'][0]['ELIGIBILITY']) && $response['data'][0]['ELIGIBILITY'] == 'Yes')  {
                        $addressInfo->setCustomAttribute('hin_status', '1');
                        $addressInfo->setCustomAttribute('hin_Start', $hinStart);
                        $addressInfo->setCustomAttribute('hin_end', $hinEnd);
                        $addressInfo->setCustomAttribute('member_id', $memberId);
                        $addressInfo->setCustomAttribute('three_four_b_id', $threeFourtyId);
                        $addressInfo->setCustomAttribute('three_four_b_start', $threeFourtyStart);
                        $addressInfo->setCustomAttribute('three_four_b_end', $threeFourtyEnd);
                        $this->addressRepository->save($addressInfo);
                        $resultRedirect->setPath('customer/index/edit', ['id' => $customerId, '_current' => true]);
                        $this->_messageManager->addSuccess("This HIN number" . " (". $hinId . ") " . "is eligible for 340B");
                        return $resultRedirect;
                }else {
                        $addressInfo->setCustomAttribute('hin_status', '0');
                        $addressInfo->setCustomAttribute('hin_Start', $hinStart);
                        $addressInfo->setCustomAttribute('hin_end', $hinEnd);
                        $addressInfo->setCustomAttribute('member_id', $memberId);
                        $addressInfo->setCustomAttribute('three_four_b_id', $threeFourtyId);
                        $addressInfo->setCustomAttribute('three_four_b_start', $threeFourtyStart);
                        $addressInfo->setCustomAttribute('three_four_b_end', $threeFourtyEnd);
                        $this->addressRepository->save($addressInfo);
                        $resultRedirect->setPath('customer/index/edit', ['id' => $customerId, '_current' => true]);
                        $this->_messageManager->addError("This HIN number" . " (". $hinId . ") " .  "is not eligible for 340B. Please check the HIN number");
                        return $resultRedirect;
                    }

                } catch (\Exception $e) {
                    throw new LocalizedException(__($e->getMessage()), $e);
                }
            } else {
                $this->_messageManager->addError(__('Please verify the address details'));
                $resultRedirect->setPath('customer/index/edit', ['id' => $customerId, '_current' => true]);
            }

            return $resultRedirect;
    }

    /**
      * Get url from store config settings
      */
    protected function getHinUrl()
    {
        return $this->scopeConfig->getValue(
            "hin_settings/general/hin_url",
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
      * Get username from store config settings
      */
    protected function getUsername()
    {
        return $this->scopeConfig->getValue(
            "hin_settings/general/hin_username",
            ScopeInterface::SCOPE_STORE
        );
    }

     /**
      * Get password from store config settings
      */
    protected function getPassword()
    {
        return $this->scopeConfig->getValue(
            "hin_settings/general/hin_password",
            ScopeInterface::SCOPE_STORE
        );
    }
}


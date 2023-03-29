<?php

namespace Ecomm\Register\Controller\Medpro;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

/**
 * Class Validate
 */
class ValidateDeaAjax extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    protected $_helper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     * @param \Ecomm\Register\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Ecomm\Register\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_helper = $helper;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);


        $request_dea_license_id = $this->getRequest()->getParam('dea_license_id');

        if($request_dea_license_id) {

            $token_data = $this->getAccessToken();

            //echo '<pre>'.print_r($token_data, true).'</pre>';

            if(isset($token_data->access_token)){

                $resultHCOS = $this->validateHcos($token_data->access_token, $request_dea_license_id);
                //echo '<pre>'.print_r($resultHCOS, true).'</pre>'; exit();
                if($resultHCOS['success'] == true){
                    $resultJson->setData($resultHCOS);

                } else {
                    $resultHCPS = $this->validateHcps($token_data->access_token, $request_dea_license_id);

                    $resultJson->setData($resultHCPS);
                }

            } else {
                $resultJson->setData([
                    'success' => false,
                    'msg'=>'Access token not generated'
                ]);
            }
        } else {
            $resultJson->setData([
                'success' => false,
                'msg'=>'Invalid license Id'
            ]);
        }

        return $resultJson;
    }

    private function validateHcos($access_token, $dea_license_id){
        $regions = $this->_regionCollectionFactory->create()->addCountryFilter('US')->getData();

        $region_id_by_code = [];
        foreach($regions as $region){
            $region_id_by_code[$region['code']] = $region['region_id'];
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.medproid.com/v1/hcos/match?deaNumber=".$dea_license_id,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 5,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$access_token
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;

        $response = json_decode($response);
        if(!isset($response->status)){

            if(isset($response->medProID)){
                $medproid = $response->medProID;

                $state_license_expiry = '';
                $state_license_status = '';
                $state_license_address = [];

                $dea_license_id = '';
                $dea_license_expiry = '';
                $dea_last_received_date = '';
                $dea_overall_eligibility = '';
                $dea_license_address = [];

                foreach($response->dea as $dea){

                    $dea_expirationDate = explode("T",$dea->expirationDate);

                    $dea_license_id = $dea->number;
                    $dea_license_expiry = $dea_expirationDate[0];
                    $dea_last_received_date = $dea->lastReceivedDate;
                    $dea_overall_eligibility = $dea->overallEligibility;
                    $dea->address->state_id = $region_id_by_code[$dea->address->state];
                    $dea_license_address = $dea->address;

                    break;

                }

                return [
                    'success' => true,
                    'medProID'=>$response->medProID,
                    'state_license_expiry'=>$state_license_expiry,
                    'state_license_status'=>$state_license_status,
                    'state_license_address'=>$state_license_address,
                    'dea_license_id'=>$dea_license_id,
                    'dea_license_expiry'=>$dea_license_expiry,
                    'dea_last_received_date'=>$dea_last_received_date,
                    'dea_overall_eligibility'=>$dea_overall_eligibility,
                    'dea_license_address'=>$dea_license_address
                ];
            } else {
                $msg = 'HCOS Invalid license Id';
                if(isset($response->title)) {
                    $msg = $response->title;
                }
                return [
                    'success' => false,
                    'msg'=>$msg
                ];

            }
        } else {

            $msg = 'HCOS Invalid license Id';
            if(isset($response->title)) {
                $msg = $response->title;
            }
            return [
                'success' => false,
                'msg'=>$msg
            ];
        }

    }

    private function validateHcps($access_token, $dea_license_id){
        $regions = $this->_regionCollectionFactory->create()->addCountryFilter('US')->getData();

        $region_id_by_code = [];
        foreach($regions as $region){
            $region_id_by_code[$region['code']] = $region['region_id'];
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.medproid.com/v1/hcps/match?deaNumber=".$dea_license_id,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 5,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$access_token
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;

        $response = json_decode($response);
        if(!isset($response->status)){

            if(isset($response->medProID)){
                $medproid = $response->medProID;


                $state_license_expiry = '';
                $state_license_status = '';
                $state_license_address = [];


                $dea_license_id = '';
                $dea_license_expiry = '';
                $dea_last_received_date = '';
                $dea_overall_eligibility = '';
                $dea_license_address = [];

                foreach($response->dea as $dea){

                    $dea_expirationDate = explode("T",$dea->expirationDate);

                    $dea_license_id = $dea->number;
                    $dea_license_expiry = $dea_expirationDate[0];
                    $dea_last_received_date = $dea->lastReceivedDate;
                    $dea_overall_eligibility = $dea->overallEligibility;
                    $dea->address->state_id = $region_id_by_code[$dea->address->state];
                    $dea_license_address = $dea->address;

                    break;

                }


                return [
                    'success' => true,
                    'medProID'=>$response->medProID,
                    'state_license_expiry'=>$state_license_expiry,
                    'state_license_status'=>$state_license_status,
                    'state_license_address'=>$state_license_address,
                    'dea_license_id'=>$dea_license_id,
                    'dea_license_expiry'=>$dea_license_expiry,
                    'dea_last_received_date'=>$dea_last_received_date,
                    'dea_overall_eligibility'=>$dea_overall_eligibility,
                    'dea_license_address'=>$dea_license_address
                ];
            } else {
                $msg = 'HCPS Invalid license Id';
                if(isset($response->title)) {
                    $msg = $response->title;
                }
                return [
                    'success' => false,
                    'msg'=>$msg
                ];

            }
        } else {

            $msg = 'HCPS Invalid license Id';
            if(isset($response->title)) {
                $msg = $response->title;
            }
            return [
                'success' => false,
                'msg'=>$msg
            ];
        }

    }

    private function getAccessToken(){

        $url = 'https://api.medproid.com/v1/authorize/token';
        //$postData = 'client_id=b2c0b706-7061-46e7-8576-393bba0d0718&client_secret=eca6b52b-9e58-46e0-a585-485fcc6c533b&grant_type=client_credentials';

        $postData = 'client_id='.$this->_helper->getClientId().'&client_secret='.$this->_helper->geClientSecret().'&grant_type=client_credentials';

        //echo $url.'<br />';
        //echo $postData.'<br />';

        //Create the headers array.
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded'
        );

        //Initiate cURL.
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        //Set the headers that we want our cURL client to use.
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //Execute the cURL request.
        $response = curl_exec($ch);

        //echo $response;

        return json_decode($response);
    }
}

<?php

namespace Ecomm\Api\Model\Api;

use Ecomm\Api\Api\PolicyTrackRepositoryInterface;
use Ecomm\Api\Api\PolicyTrack\PolicyTrackInterface;

class PolicyTrack implements PolicyTrackRepositoryInterface{

    /**
     * Constructor
     * \Ecomm\Api\Model\PolicyTrack $policyTrack
     *
     */
    public function __construct(
        \Ecomm\Api\Model\PolicyTrack $policyTrack,
        \Magento\Framework\App\Request\Http $http,
        \Ecomm\Api\Model\ResourceModel\PolicyTrack $policyTrackModel,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory
    ){
        $this->policyTrack         = $policyTrack;
        $this->http                     = $http;
        $this->policyTrackModel    = $policyTrackModel;
        $this->tokenFactory             = $tokenFactory;
    }

    public function save(PolicyTrackInterface $data)
    {

        //echo '<pre>'.print_r($data->getData(), true).'</pre>';
        try {
            $policyTrackDetails = $this->policyTrack->load($data->getEmail(), 'email');
            if(!$policyTrackDetails->getId()) {
                if($data->getTcVersion() != $policyTrackDetails->getTcVersion() || $data->getEulaVersion() != $policyTrackDetails->getEulaVersion()) {
                    $policyTrackDetails->setData($data->getData());
                }
            } else {
                $policyTrackDetails->setEmail($data->getEmail());
                $policyTrackDetails->setTcVersion($data->getTcVersion());
                $policyTrackDetails->setEulaVersion($data->getEulaVersion());
            }
            $policyTrackDetails->save();

        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the data: %1',
                $exception->getMessage()
            ));
        }
        return 'success';
    }
}
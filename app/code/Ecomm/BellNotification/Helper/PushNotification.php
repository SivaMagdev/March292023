<?php

namespace Ecomm\BellNotification\Helper;

use \Psr\Log\LoggerInterface;

class PushNotification extends \Magento\Framework\App\Helper\AbstractHelper
{
    const API_ACCESS_KEY = "AAAAZBVsJj0:APA91bFxA1_8UOJtb3S5SxO640jcBlCNuBNTwL8y6Fb49d_fExz0WsairhKZ39jinEjYDpMdeOA2R148VY2M_iyH5BxnGOWcuu7BVUSTt2DaZ32M7sc8mbsfmScDUf3EJE6V-WFgGJV4";//Api Access key for android.

    const PASSPHASE = "1234"; // passphase for IOS

    protected $dir;

    protected $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     */
    public function __construct(
        \Ecomm\BellNotification\Model\PushNotification $pushNotification,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        LoggerInterface $logger
    ) {
        $this->dir = $dir;
        $this->pushNotification = $pushNotification;
        $this->logger               = $logger;
        parent::__construct($context);
    }


    /**
     * @return string
     */
    public function getApiAccessKey() : string
    {
        return (string) $this->scopeConfig->getValue('ecomm_bellnotification/general/api_access_key');
    }

    /**
     * @return string
     */
    public function getPassPhase() : string
    {
        return (string) $this->scopeConfig->getValue('ecomm_bellnotification/general/passphase');
    }

    /**
     * @return string
     */
    public function getMode() : string
    {
        return (string) $this->scopeConfig->getValue('ecomm_bellnotification/general/mode');
    }

    /**
     * @return string
     */
    public function getIosSandboxUrl() : string
    {
        return (string) $this->scopeConfig->getValue('ecomm_bellnotification/general/sandbox_url');
    }

    /**
     * @return string
     */
    public function getIosLiveUrl() : string
    {
        return (string) $this->scopeConfig->getValue('ecomm_bellnotification/general/live_url');
    }

    /**
     * @return string
     */
    public function getIosPort() : string
    {
        return (string) $this->scopeConfig->getValue('ecomm_bellnotification/general/port');
    }

    /**
     * @return string
     */
    public function getIosBundleId() : string
    {
        return (string) $this->scopeConfig->getValue('ecomm_bellnotification/general/bundle_id');
    }

    public function sendPushNotification($type, $title, $message, $customer_id){

        //echo $this->getApiAccessKey();
        //echo $this->getPassPhase();
        //echo $this->getMode();
        $pushNotificationDetails = $this->pushNotification->load($customer_id, 'customer_id');

        if($pushNotificationDetails->getId()){
            if($pushNotificationDetails->getDeviceToken() != '') {
                if($pushNotificationDetails->getDeviceType() == 'android'){
                    $this->sendAndroidPushNotification($type, $title, $message, $pushNotificationDetails->getDeviceToken());
                }
                if($pushNotificationDetails->getDeviceType() == 'ios'){
                    //$this->sendIosPushNotification($type, $message,$pushNotificationDetails->getDeviceToken());
                    $this->sendIosPushNotification2($type, $message,$pushNotificationDetails->getDeviceToken());
                }
            }
        }
    }

    /**
     * send push notification for ios
     */
    public function sendIosPushNotification2($type, $message,$devicetoken){

        $pem_file = $this->dir->getPath('media').'/iospemfile/pushcert.pem';

        $pem_secret = $this->getPassPhase();

        $body['aps'] = array(
            'alert' => $message,
            'category' => $type,
            'sound' => 'default',
            'badge' => 0
        );
        // Encode the payload as JSON
        $message = json_encode($body);
        //$message = '{"aps":{"alert":"Hi! TEST","sound":"default"}}';

        if($this->getMode() == 'live'){
            //$applegateway='https://api.push.apple.com';//for production mode
            $http2_server = $this->getIosLiveUrl(); //for production mode
        } else {
            //$applegateway='https://api.sandbox.push.apple.com';//for sanbox mode
            $http2_server = $this->getIosSandboxUrl(); //for sanbox mode
        }

        $app_bundle_id = $this->getIosBundleId();

        $url = "{$http2_server}/3/device/{$devicetoken}";

        //$this->logger->log('ERROR','Getway URL: ',[$url]);
        //$this->logger->log('ERROR','app_bundle_id URL: ',[$app_bundle_id]);

        //echo $url;

        $headers = array(
            "apns-topic: {$app_bundle_id}"
        );

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_PORT, $this->getIosPort());
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSLCERT, $pem_file);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $pem_secret);
        $result = curl_exec($ch);
        curl_close( $ch );

        if (!$result) {
            $this->logger->log('ERROR','Push Notification Not Sent',[$result]);
        } else {
            $this->logger->log('ERROR','Push Notification Sent successfully: ',[$result]);
        }

    }

    /**
     * send push notification for ios
     */
    public function sendIosPushNotification($type, $message,$devicetoken){
        $mediaPath = $this->dir->getPath('media');
        $tCert = $mediaPath.'/iospemfile/pushcert.pem';// your certificates file location

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $tCert);
        //stream_context_set_option($ctx, 'ssl', 'passphrase', self::PASSPHASE);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $this->getPassPhase());

        if($this->getMode() == 'live'){
            //$applegateway='ssl://gateway.push.apple.com:2195';//for production mode
            $applegateway = $this->getIosLiveUrl(); //for production mode
        } else {
            //$applegateway='ssl://gateway.sandbox.push.apple.com:2195';//for sanbox mode
            $applegateway = $this->getIosSandboxUrl(); //for sanbox mode
        }

        $this->logger->log('ERROR','Gateway URL:',[$applegateway]);

        $fp = stream_socket_client($applegateway, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

        if(!$fp){
            //exit("Failed to connect: $err $errstr" . PHP_EOL);
            $this->logger->log('ERROR','Failed to connect:',[$errstr]);
        } else {
            $body['aps'] = array(
                'alert' => $message,
                'category' => $type,
                'sound' => 'default',
                'badge' => 0
            );
            // Encode the payload as JSON
            $payload = json_encode($body);

            $this->logger->log('ERROR','IOS Notification payload:',[$payload]);
            // Build the binary notification
            $msg = chr(0) . pack('n', 32) . pack('H*', $devicetoken) . pack('n', strlen($payload)) . $payload;
            //echo $msg;
            // Send it to the server
            $result = fwrite($fp, $msg, strlen($msg));
            $this->logger->log('ERROR','Result:',[$result]);
            //echo $result; die();
            //set blocking
            stream_set_blocking($fp,0);
            //usleep(500000);
            //Check response
            $apple_error_response = fread($fp, 6);
            $this->logger->log('ERROR','Error Response-1:',[$apple_error_response]);
            if($apple_error_response != ''){
                //print_r($apple_error_response, true); exit();
                $error_response = unpack('Ccommand/Cstatus_code/Nidentifier', $apple_error_response); //unpack the error response (first byte 'command" should always be 8)

                $this->logger->log('ERROR','Error Response-2:',[$error_response]);
            }
            // Close the connection to the server
            fclose($fp);

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $log = $objectManager->get('\Psr\Log\LoggerInterface');
            $level = 'ERROR';

            if (!$result) {
                //$log->debug($level,['data' => 'Message not delivered' . PHP_EOL]);
                $this->logger->log('ERROR','Message not delivered',[$result]);
            } else {
                //$log->debug($level,['data' => 'Message successfully delivered' . PHP_EOL]);
                $this->logger->log('ERROR','Message successfully delivered',[$result]);
            }
        }
    }

    /**
     * send push notification for android
     */
    public function sendAndroidPushNotification($type, $title, $message,$fcmtoken){

        $data = array('title' => $title,'body' => $message, 'category' => $type) ;
        $notification = array('title' => $title,'body' => $message, 'category' => $type, 'click_action' => 'fcm.ACTION.NOTIF') ;
        $fields = array('to' => $fcmtoken,'data'  => $data,'notification' => $notification);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $log = $objectManager->get('\Psr\Log\LoggerInterface');

        $log->debug('Android Push Notification: '.json_encode($fields));

        /*$headers = array(
            'Authorization: key=' . self::API_ACCESS_KEY,
            'Content-Type: application/json'
        );*/
        $headers = array(
            'Authorization: key=' . $this->getApiAccessKey(),
            'Content-Type: application/json'
        );

        //echo '<pre>'.print_r($fields, true).'</pre>';

        #Send Reponse To FireBase Server
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        //echo $result;
        $log->debug('Android Push Notification: '.$result);
     }

    /**
     * @return array
     */
    public function getModes()
    {
        return [
            [
                'value' => 'sandbox',
                'label' => 'Sand-box',
            ],
            [
                'value' => 'live',
                'label' => 'Live',
            ],
        ];
    }

}
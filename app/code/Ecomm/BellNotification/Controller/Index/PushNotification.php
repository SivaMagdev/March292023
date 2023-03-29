<?php

namespace Ecomm\BellNotification\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;

class PushNotification extends Action
{
    protected $resultPageFactory;

    protected $session;

    protected $resultRedirectFactory;

    protected $dir;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $session,
        \Ecomm\BellNotification\Model\BellNotification $bellNotification,
        RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $session;
        $this->bellNotification = $bellNotification;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->dir = $dir;
    }

    public function execute()
    {
        /*$mediaPath = $this->dir->getPath('media');
        $tCert = $mediaPath.'/iospemfile/pushcert.pem';// your certificates file location

        // open connection
        $http2ch = curl_init();
        curl_setopt($http2ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);

        // send push
        $pem_file = $mediaPath.'/iospemfile/pushcert.pem';
        $pem_secret = '1234';

        $message = '{"aps":{"alert":"Hi! TEST","sound":"default"}}';
        $token = 'f658639a00b67a7a0423e9daa908d2d89a9f800816bc304003972c59563ade49---';
        $http2_server = 'https://api.push.apple.com'; // or 'api.push.apple.com' if production
        $app_bundle_id = 'com.drl.drreddysdirect';

        $url = "{$http2_server}/3/device/{$token}";

        //echo $url;

        $headers = array(
            "apns-topic: {$app_bundle_id}"
        );

        //echo '<pre>'.print_r($headers,).'</pre>';

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, $url );
        curl_setopt( $ch,CURLOPT_PORT, 443 );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, $message );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_TIMEOUT, 30 );
        curl_setopt($ch, CURLOPT_SSLCERT, $pem_file);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $pem_secret);
        $result = curl_exec($ch );

        echo 'result: '.$result;
        curl_close( $ch );*/

        /*$status = $this->sendHTTP2Push($http2ch, $http2_server, $apple_cert, $app_bundle_id, $message, $token);
        echo "Response from apple -> {$status}\n";

        // close connection
        curl_close($http2ch);*/
    }

    private function sendHTTP2Push($http2ch, $http2_server, $apple_cert, $app_bundle_id, $message, $token) {

        // url (endpoint)
        $url = "{$http2_server}/3/device/{$token}";

        echo $url;

        // certificate
        $cert = realpath($apple_cert);

        // headers
        $headers = array(
            "apns-topic: {$app_bundle_id}",
            "User-Agent: My Sender"
        );

        // other curl options
        curl_setopt_array($http2ch, array(
            CURLOPT_URL => $url,
            CURLOPT_PORT => 443,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => $message,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSLCERT => $cert,
            CURLOPT_HEADER => 1
        ));

        // go...
        $result = curl_exec($http2ch);
        if ($result === FALSE) {
          throw new Exception("Curl failed: " .  curl_error($http2ch));
        }

        // get response
        $status = curl_getinfo($http2ch, CURLINFO_HTTP_CODE);

        return $status;
    }
}
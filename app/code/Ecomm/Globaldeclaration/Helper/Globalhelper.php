<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecomm\Globaldeclaration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;

class Globalhelper extends AbstractHelper
{
	protected $transportBuilder;
    protected $storeManager;
    protected $inlineTranslation;
    protected $bellNotification;
    protected $resourceConnection;
 
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        \Ecomm\BellNotification\Helper\BellNotification $bellNotification,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        ResourceConnection $resourceConnection
    )
    {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->bellNotification = $bellNotification;
        $this->customerRepository = $customerRepository;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }
 
    public function customerPriceBellEmailNotification($customerId = 0){

        try{
            $customerFlag = false;
            if(!empty(($customerId))){
                $customer = $this->getCustomer($customerId);
                if($customer->getId()){
                    $customerFlag = true;
                }
            }
            if(!empty($customerFlag)){
                $connection = $this->resourceConnection->getConnection();
                $query = "select 
                *
                from ecomm_price_decrease_notify
                WHERE customer_id='".$customerId."' AND notified='No'";
                $result = $connection->fetchAll($query);
                if((!empty($customer)) && (!empty($result))){
                    $templateVars = array();
                    $templateVars['name'] = $customer->getFirstName();
                    $templateVars['email'] = $customer->getEmail();
                    $templateVars['products'] = $result;

                    //echo "<pre>";print_r($result);die;
                    
                    //$templateId = 'drd_price_notification_drd_price_notification_group_regular_price_notification_email_template';
                    $templateId = $this->adminStoreConfigurationValue('drd_price_notification/drd_price_notification_group/price_notification_email_template');
                    $fromEmailName = $this->adminStoreConfigurationValue('drd_price_notification/drd_price_notification_group/price_from_emails_name');
                    $fromEmail = $this->adminStoreConfigurationValue('drd_price_notification/drd_price_notification_group/price_from_emails');
                    $ccEmail = $this->adminStoreConfigurationValue('drd_price_notification/drd_price_notification_group/price_cc_emails');
                    foreach ($result as $key => $value) {
                        $bellNotificationMessage = $this->adminStoreConfigurationValue('drd_price_notification/drd_price_notification_group/price_bell_notification_message_text');
                        if((!empty($bellNotificationMessage)) && ($bellNotificationMessage)){
                            $bellNotificationMessage = str_replace("{NDC}", $value['product_sku'], $bellNotificationMessage);
                            $bellNotificationMessage = str_replace("{pricetye}", $value['product_price_type'], $bellNotificationMessage);
                            $bellNotificationMessage = str_replace("{pricename}", $value['product_name'], $bellNotificationMessage);
                            $this->bellNotification->pushToNotification(3,$customerId,'Sales Order',$bellNotificationMessage);            
                        }
                    }
                    if((!empty($fromEmailName)) && (!empty($fromEmail)) && ($fromEmailName) && ($fromEmail)){
                        $emailNotification = $this->sendEmail($templateId, $fromEmailName, $fromEmail, $templateVars['email'], $templateVars, $ccEmail);
                    }
                }
                return true;
            }
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }

    }

    public function customerRegularPriceNotification($product, $priceType, $regularPrice = 0){
    	/* Code to notify customer for regular price change start */
    	if(!empty($priceType)){
    		try {
                $noOfDays = $this->adminStoreConfigurationValue('drd_price_notification/drd_price_notification_group/price_notify_no_of_days');
                $noOfDays = $noOfDays ? $noOfDays : 90;
                //echo "<pre>";print_r($noOfDays);die;
		        $connection = $this->resourceConnection->getConnection();
		        $query = "select 
				DISTINCT sales_order.customer_id
				from sales_order
				INNER JOIN sales_order_item
				ON sales_order.entity_id=sales_order_item.order_id
				WHERE sales_order_item.product_id = '".$product->getEntityId()."' AND sales_order.customer_id IS NOT NULL AND sales_order_item.price > ".$regularPrice." AND sales_order_item.price_type like '".$priceType."' AND sales_order.created_at + INTERVAL ".$noOfDays." DAY > NOW()";
		        $result = $connection->fetchAll($query);

                //echo "<pre>";print_r($result);die;
		        foreach ($result as $key => $value) {
                    $data = [
                        'customer_id' => $value['customer_id'],
                        'product_id' => $product->getEntityId(),
                        'product_sku' => $product->getSku(),
                        'product_name' => $product->getName(),
                        'product_price' => $regularPrice,
                        'product_price_type' => $priceType,
                        'notified' => 'No'
                    ];

                    $connection->insert('ecomm_price_decrease_notify', $data);		        	
		        }
	        	return true;
	        } catch (\Exception $e) {
            	$this->_logger->info($e->getMessage());
        	}
    	}else{
    		return false;
    	}

        /* Code to notify customer for regular price change end */
    }

    public function sendEmail($templateId, $fromEmailName, $fromEmail, $toEmail, $templateVars, $ccEmail = '')
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
 
            $from = ['email' => $fromEmail, 'name' => "DRL Admin"];
            //$from = ['email' => $fromEmail];
            $this->inlineTranslation->suspend();
 
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            
            if(!empty($ccEmail)){
            	$ccEmail = explode(",",$ccEmail);
            	$transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
            	->addCC($ccEmail)
                ->getTransport();
            }else{
            	$transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();
            }
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }

	/* Pass customer id $id*/
	public function getCustomer($id)
	{ 
		return $this->customerRepository->getById($id);
	}

	public function adminStoreConfigurationValue($storeConfigPath = ""){
		if(!empty($storeConfigPath)){
			return $this->scopeConfig->getValue($storeConfigPath);
		}else{
			return false;
		}
	}
}


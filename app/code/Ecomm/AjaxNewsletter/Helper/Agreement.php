<?php
namespace Ecomm\AjaxNewsletter\Helper;

    class Agreement extends \Magento\Framework\App\Helper\AbstractHelper 
    { 

        /** * @var \Magento\Framework\App\Config\ScopeConfigInterfac 
            */ 
        protected $_scopeConfig; 
        CONST ENABLE = 'drl_consent/drl_consent_group/agreement_text'; 


        public function __construct( \Magento\Framework\App\Helper\Context $context, 
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig ) {

             parent::__construct($context); $this->_scopeConfig = $scopeConfig;
    }

    public function getEnable(){
        return $this->_scopeConfig->getValue(self::ENABLE);
    }


}
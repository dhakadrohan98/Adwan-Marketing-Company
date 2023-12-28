<?php
    
    namespace WebGate\SMSAShipping\Helper;
    
    use Magento\Framework\App\Helper\AbstractHelper;
    use Magento\Framework\App\Helper\Context;
    use Magento\Store\Model\ScopeInterface;
    
    class Data extends AbstractHelper
    {
        const base = 'WebGate/SMSAShipping/';
        
        /**
         * @param Context $context
         */
        public function __construct(
            Context $context
        )
        {
            parent::__construct($context);
        }
    
        /**
         * @return string
         */
        public function getPassKey()
        {
            return $this->getConfigValue('passKey' , static::base);
        }
    
        /**
         * @return string
         */
        public function getShipperName()
        {
            return $this->getConfigValue('shipper_name' , static::base);
        }
    
        /**
         * @return string
         */
        public function getShipperContact()
        {
            return $this->getConfigValue('shipper_contact' , static::base);
        }
    
        /**
         * @return string
         */
        public function getShipperPhone()
        {
            return $this->getConfigValue('shipper_phone' , static::base);
        }
    
        /**
         * @return string
         */
        public function getShipperAddress()
        {
            return $this->getConfigValue('shipper_address' , static::base);
        }
    
        /**
         * @return string
         */
        public function getShipperCity()
        {
            return $this->getConfigValue('shipper_city' , static::base);
        }
    
        /**
         * @return string
         */
        public function getShipperCountry()
        {
            return $this->getConfigValue('shipper_country' , static::base);
        }
        
        /**
         * @return string
         */
        public function getAPIUrl()
        {
            return $this->getConfigValue('api_url' , static::base);
        }
    
        private function getConfigValue($code , $path , $storeId = null)
        {
            return $this->scopeConfig->getValue(
                $path . $code , ScopeInterface::SCOPE_STORE , $storeId
            );
        }
    }

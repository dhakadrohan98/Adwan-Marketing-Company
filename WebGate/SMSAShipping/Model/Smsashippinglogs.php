<?php
    
    namespace WebGate\SMSAShipping\Model;
    
    use Magento\Framework\Model\AbstractModel;
    
    class Smsashippinglogs extends AbstractModel
    {
        /**
         * @var string
         */
        protected $_cacheTag = 'webgate_smsashipping_smsashippinglogs';
    
        /**
         * Prefix of model events names
         *
         * @var string
         */
        protected $_eventPrefix = 'webgate_smsashipping_smsashippinglogs';
    
        /**
         * Initialize resource model
         *
         * @return void
         */
        protected function _construct()
        {
            parent::_construct();
            $this->_init('WebGate\SMSAShipping\Model\ResourceModel\Smsashippinglogs');
        }
    }

<?php
    
    namespace WebGate\SMSAShipping\Model\ResourceModel\Smsashippinglogs;
    
    use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
    
    class Collection extends AbstractCollection
    {
        /**
         * @var string
         */
        protected $_idFieldName = 'entity_id';
    
        /**
         * Define resource model
         *
         * @return void
         */
        protected function _construct()
        {
            $this->_init('WebGate\SMSAShipping\Model\Smsashippinglogs', 'WebGate\SMSAShipping\Model\ResourceModel\Smsashippinglogs');
        }
    }

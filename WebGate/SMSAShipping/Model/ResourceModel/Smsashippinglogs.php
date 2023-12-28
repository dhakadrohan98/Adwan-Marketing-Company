<?php
    
    namespace WebGate\SMSAShipping\Model\ResourceModel;
    
    use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
    
    class Smsashippinglogs extends AbstractDb
    {
        /**
         * Initialize resource model
         *
         * @return void
         */
        protected function _construct()
        {
            $this->_init('webgate_smsashipping_smsashippinglogs', 'entity_id');
        }
    }

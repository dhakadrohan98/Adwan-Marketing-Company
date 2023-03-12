<?php
    
    namespace WebGate\SMSAShipping\Block\Frontend;
    
    use Magento\Catalog\Model\Session;
    use Magento\Framework\Registry;
    use Magento\Framework\View\Element\Template;
    use WebGate\SMSAShipping\Helper\SMSA;
    
    class AddBtnMyOrder extends Template
    {
        /**
         * @var Registry
         */
        private $registry;
        /**
         * @var SMSA
         */
        private $SMSA;
        /**
         * @var Session
         */
        private $session;
        
        public function __construct(
            Registry $registry ,
            Session $session ,
            SMSA $SMSA ,
            
            Template\Context $context ,
            array $data = []
        )
        {
            parent::__construct($context , $data);
            $this->registry = $registry;
            $this->SMSA = $SMSA;
            $this->session = $session;
        }
        
        /**
         * @return string
         */
        public function getAwdStatus()
        {
            return $this->getOrder()->getData('awd_status');
        }
        
        /**
         * @return \Magento\Sales\Model\Order\Interceptor
         */
        private function getOrder()
        {
            return $this->registry->registry('current_order');
        }
        
        /**
         * @return int
         */
        public function getAwdNumber()
        {
            return $this->getOrder()->getData('awd_number');
        }
        
        /**
         * @return bool
         */
        public function isAwbMethod()
        {
            return $this->getOrder()->getShippingMethod() == 'awb_method_awb_method';
        }
        
        /**
         * @return int
         */
        public function getOrderId()
        {
            return $this->getOrder()->getId();
        }
        
        /**
         * @return array
         */
        public function getTracking()
        {
            $tracking = $this->session->getAvbTracking();
            if(!empty($tracking))
            {
                $this->session->setAvbTracking('');
            }
            return $tracking;
        }
    }
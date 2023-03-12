<?php
    
    
    namespace WebGate\SMSAShipping\Model\Carrier;
    
    use Magento\Framework\App\Config\ScopeConfigInterface;
    use Magento\Quote\Model\Quote\Address\RateRequest;
    use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
    use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
    use Magento\Shipping\Model\Carrier\AbstractCarrier;
    use Magento\Shipping\Model\Carrier\CarrierInterface;
    use Magento\Shipping\Model\Rate\ResultFactory;
    use Psr\Log\LoggerInterface;

    class AwbMethod extends AbstractCarrier implements CarrierInterface
    {
        
        protected $_code = 'awb_method';
        
        protected $_isFixed = true;
        
        protected $_rateResultFactory;
        
        protected $_rateMethodFactory;
        
        /**
         * Constructor
         *
         * @param ScopeConfigInterface          $scopeConfig
         * @param ErrorFactory  $rateErrorFactory
         * @param LoggerInterface                                    $logger
         * @param ResultFactory                  $rateResultFactory
         * @param MethodFactory $rateMethodFactory
         * @param array                                                       $data
         */
        public function __construct(
            ScopeConfigInterface $scopeConfig ,
            ErrorFactory $rateErrorFactory ,
            LoggerInterface $logger ,
            ResultFactory $rateResultFactory ,
            MethodFactory $rateMethodFactory ,
            array $data = []
        )
        {
            $this->_rateResultFactory = $rateResultFactory;
            $this->_rateMethodFactory = $rateMethodFactory;
            parent::__construct($scopeConfig , $rateErrorFactory , $logger , $data);
        }
        
        /**
         * {@inheritdoc}
         */
        public function collectRates(RateRequest $request)
        {
            if(!$this->getConfigFlag('active'))
            {
                return false;
            }
            
            $shippingPrice = $this->getConfigData('price');
            
            $result = $this->_rateResultFactory->create();
            
            if($shippingPrice !== false)
            {
                $method = $this->_rateMethodFactory->create();
                
                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->getConfigData('title'));
                
                $method->setMethod($this->_code);
                $method->setMethodTitle($this->getConfigData('name'));
                
//                if($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes())
                if($request->getPackageQty() == $this->getFreeBoxes())
                {
                    $shippingPrice = '0.00';
                }
                
                $method->setPrice($shippingPrice);
                $method->setCost($shippingPrice);
                
                $result->append($method);
            }
            
            return $result;
        }
        
        /**
         * getAllowedMethods
         *
         * @param array
         */
        public function getAllowedMethods()
        {
            return [ 'flatrate' => $this->getConfigData('name') ];
        }
    }

<?php namespace WebGate\SMSAShipping\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;
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
    
    public function __construct(
        Registry $registry ,
        SMSA $SMSA ,
        Template\Context $context ,
        array $data = []
    )
    {
        parent::__construct($context , $data);
        $this->registry = $registry;
        $this->SMSA = $SMSA;
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
    
}

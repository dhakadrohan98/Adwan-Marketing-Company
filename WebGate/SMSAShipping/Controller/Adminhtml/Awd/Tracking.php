<?php
    
    namespace WebGate\SMSAShipping\Controller\Adminhtml\Awd;
    
    use Exception;
    use Magento\Backend\App\Action\Context;
    use Magento\Framework\Controller\Result\Json;
    use Magento\Framework\Controller\Result\JsonFactory;
    use Magento\Framework\View\Result\PageFactory;
    use Magento\Sales\Model\OrderFactory;
    use WebGate\SMSAShipping\Helper\SMSA;
    
    class Tracking extends \Magento\Framework\App\Action\Action
    {
        
        /**
         * @var OrderFactory
         */
        private $orderFactory;
        /**
         * @var SMSA
         */
        private $SMSA;
        /**
         * @var JsonFactory
         */
        private $resultJsonFactory;
        
        
        /**
         * @param Context     $context
         * @param PageFactory $resultPageFactory
         */
        public function __construct(
            Context $context ,
            JsonFactory $resultJsonFactory ,
            OrderFactory $orderFactory ,
            SMSA $SMSA
        )
        {
            parent::__construct($context);
            $this->orderFactory = $orderFactory;
            $this->SMSA = $SMSA;
            $this->resultJsonFactory = $resultJsonFactory;
        }
        
        /**
         * Index action
         *
         * @return Json
         */
        public function execute()
        {
            $order_id = $this->getRequest()->getParam('order_id');
            $order = $this->orderFactory->create()->load($order_id);
            $tracking = $this->SMSA->getTracking($order->getData('awd_number'));
            
            if($tracking instanceof Exception)
            {
                $data = [
                    'message' => __('get Tracking error : ') . $tracking->getMessage() ,
                    'success' => false ,
                    'data' => '' ,
                ];
            }
            else
            {
                $data = [
                    'message' => __('get Tracking Success') ,
                    'success' => true ,
                    'data' => $tracking ,
                ];
            }
            
            
            /** @var Json $result */
            $result = $this->resultJsonFactory->create();
            return $result->setData($data);
            
        }
    }

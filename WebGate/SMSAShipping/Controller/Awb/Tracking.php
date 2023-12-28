<?php
    
    namespace WebGate\SMSAShipping\Controller\Awb;
    
    use Exception;
    use Magento\Catalog\Model\Session;
    use Magento\Framework\App\Action\Action;
    use Magento\Framework\App\Action\Context;
    use Magento\Framework\Controller\ResultFactory;
    use Magento\Framework\View\Result\PageFactory;
    use Magento\Sales\Model\OrderFactory;
    use WebGate\SMSAShipping\Helper\SMSA;
    
    class Tracking extends Action
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
         * @var Session
         */
        private $session;
        
        
        /**
         * @param Context     $context
         * @param PageFactory $resultPageFactory
         */
        public function __construct(
            Session $session ,
            Context $context ,
            OrderFactory $orderFactory ,
            SMSA $SMSA
        )
        {
            parent::__construct($context);
            $this->orderFactory = $orderFactory;
            $this->SMSA = $SMSA;
            $this->session = $session;
        }
        
        /**
         * Index action
         *
         * @return \Magento\Backend\Model\View\Result\Page
         */
        public function execute()
        {
            $order_id = $this->getRequest()->getParam('order_id');
            $order = $this->orderFactory->create()->load($order_id);
            $tracking = $this->SMSA->getTracking($order->getData('awd_number'));
            
            if($tracking instanceof Exception)
            {
                $this->messageManager->addErrorMessage(__('get Tracking error : '));
                $this->session->setAvbTracking([]);
            }
            else
            {
                $this->messageManager->addSuccessMessage(__('get Tracking Success'));
                $this->session->setAvbTracking($tracking);
            }
    
    
            /** @var \Magento\Backend\Model\View\Result\Page $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
            
        }
    }

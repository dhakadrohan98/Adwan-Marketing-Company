<?php
    
    namespace WebGate\SMSAShipping\Controller\Awb;
    
    use Exception;
    use Magento\Framework\App\Action\Action;
    use Magento\Framework\App\Action\Context;
    use Magento\Framework\Controller\ResultFactory;
    use Magento\Framework\View\Result\PageFactory;
    use Magento\Sales\Model\OrderFactory;
    use WebGate\SMSAShipping\Helper\SMSA;
    
    class Status extends Action
    {
        /**
         * @var PageFactory
         */
        protected $resultPageFactory;
        /**
         * @var OrderFactory
         */
        private $orderFactory;
        /**
         * @var SMSA
         */
        private $SMSA;
        
        /**
         * @param Context     $context
         * @param PageFactory $resultPageFactory
         */
        public function __construct(
            Context $context ,
            PageFactory $resultPageFactory ,
            OrderFactory $orderFactory ,
            SMSA $SMSA
        )
        {
            parent::__construct($context);
            $this->resultPageFactory = $resultPageFactory;
            $this->orderFactory = $orderFactory;
            $this->SMSA = $SMSA;
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
            $status = $this->SMSA->getStatus($order->getData('awd_number'));
            
            if($status instanceof Exception)
            {
                $this->messageManager->addErrorMessage(__('awb_status update error' ));
            }
            else
            {
                $order->addData([
                    'awd_status' => $status ,
                ])->save();
                $this->messageManager->addSuccessMessage(__('awb_status update'));
            }
            
            /** @var \Magento\Backend\Model\View\Result\Page $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }

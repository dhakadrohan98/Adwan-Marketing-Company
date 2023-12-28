<?php
    
    namespace WebGate\SMSAShipping\Controller\Adminhtml\Awd;
    
    use Exception;
    use Magento\Backend\App\Action\Context;
    use Magento\Framework\Controller\ResultFactory;
    use Magento\Framework\View\Result\PageFactory;
    use Magento\Sales\Model\OrderFactory;
    use WebGate\SMSAShipping\Helper\SMSA;
    
    class Pdf extends \Magento\Framework\App\Action\Action
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
            $base64 = $this->SMSA->downloadPdf($order->getData('awd_number'));

            if($base64 instanceof Exception)
            {
                $this->messageManager->addErrorMessage(__('get pdf error : ' ). $base64->getMessage());
            }
            
            /** @var \Magento\Backend\Model\View\Result\Page $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }

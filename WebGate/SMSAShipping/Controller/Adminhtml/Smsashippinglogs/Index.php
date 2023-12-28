<?php
    
    namespace WebGate\SMSAShipping\Controller\Adminhtml\Smsashippinglogs;
    
    use Magento\Backend\App\Action;
    use Magento\Backend\App\Action\Context;
    use Magento\Framework\View\Result\PageFactory;
    
    class Index extends Action
    {
        /**
         * @var PageFactory
         */
        protected $resultPageFactory;
    
        /**
         * @param Context $context
         * @param PageFactory $resultPageFactory
         */
        public function __construct(
            Context $context,
            PageFactory $resultPageFactory
        ) {
            parent::__construct($context);
            $this->resultPageFactory = $resultPageFactory;
        }
        
        /**
         * Check the permission to run it
         *
         * @return boolean
         */
        protected function _isAllowed()
        {
            return $this->_authorization->isAllowed('WebGate_SMSAShipping::smsashippinglogs');
        }
    
        /**
         * Index action
         *
         * @return \Magento\Backend\Model\View\Result\Page
         */
        public function execute()
        {
            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('WebGate_SMSAShipping::smsashippinglogs');
            $resultPage->getConfig()->getTitle()->prepend(__('Smsa shipping logs'));
    
            return $resultPage;
        }
    }

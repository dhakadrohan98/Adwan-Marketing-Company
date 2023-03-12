<?php
    
    namespace WebGate\SMSAShipping\Controller\Adminhtml\Smsashippinglogs;
    
    use Magento\Backend\App\Action;
    use Magento\Backend\App\Action\Context;
    use WebGate\SMSAShipping\Model\SmsashippinglogsFactory;
    
    class Delete extends Action
    {
        /** @var smsashippinglogsFactory $objectFactory */
        protected $objectFactory;
    
        /**
         * @param Context $context
         * @param SmsashippinglogsFactory $objectFactory
         */
        public function __construct(
        Context $context,
        SmsashippinglogsFactory $objectFactory
        ) {
            $this->objectFactory = $objectFactory;
            parent::__construct($context);
        }
    
        /**
         * {@inheritdoc}
         */
        protected function _isAllowed()
        {
            return $this->_authorization->isAllowed('WebGate_SMSAShipping::smsashippinglogs');
        }
    
        /**
         * Delete action
         *
         * @return \Magento\Framework\Controller\ResultInterface
         */
        public function execute()
        {
            $resultRedirect = $this->resultRedirectFactory->create();
            $id = $this->getRequest()->getParam('entity_id', null);
    
            try {
                $objectInstance = $this->objectFactory->create()->load($id);
                if ($objectInstance->getId()) {
                    $objectInstance->delete();
                    $this->messageManager->addSuccessMessage(__('You deleted the record.'));
                } else {
                    $this->messageManager->addErrorMessage(__('Record does not exist.'));
                }
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
            
            return $resultRedirect->setPath('*/*');
        }
    }

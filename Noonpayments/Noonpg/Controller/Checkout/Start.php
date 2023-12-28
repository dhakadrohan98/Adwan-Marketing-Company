<?php
// noon payments v2.1.1...
namespace Noonpayments\Noonpg\Controller\Checkout;

class Start extends \Magento\Framework\App\Action\Action {
	  /**
    * @var \Magento\Checkout\Model\Session
    */
    protected $_checkoutSession;

    /**
    * @var \Coinbase\Magento2PaymentGateway\Model\PaymentMethod
    */
    protected $_paymentMethod;

	protected $_resultJsonFactory;
	
	protected $_logger;
	
    /**
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Checkout\Model\Session $checkoutSession
    * @param \Coinbase\Magento2PaymentGateway\Model\PaymentMethod $paymentMethod
    */
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Checkout\Model\Session $checkoutSession,
    \Noonpayments\Noonpg\Model\PaymentMethod $paymentMethod,
	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,	
	\Psr\Log\LoggerInterface $logger
    ) {
        $this->_paymentMethod = $paymentMethod;
        $this->_checkoutSession = $checkoutSession;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_logger = $logger;		
		
        parent::__construct($context);
		
		//$this->_checkoutSession->restoreQuote();
    }

    /**
    * Start checkout by requesting checkout code and dispatching customer to Coinbase.
    */

    public function execute() {
        //$this->_logger->debug('Entry Start Execute-'); 
		$html = $this->_paymentMethod->buildCheckoutRequest($this->getOrder());
		
		$this->messageManager->getMessages(true);//clear previous messages
		
		if(isset($html['error']) && $html['error']!="") {
			$this->_logger->error("noonpg Error-".json_encode($html)); 	
			$this->messageManager->addError("<strong>Error:</strong> ".$html['error']);
		}

		$result = $this->_resultJsonFactory->create();
		return $result->setData(['html' => $html['data']]);

    }
	
	 /**
    * Get order object.
    *
    * @return \Magento\Sales\Model\Order
    */
    protected function getOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }

}

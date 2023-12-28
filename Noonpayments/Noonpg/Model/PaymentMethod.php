<?php
// noon payments v2.1.1...
//noon payment plugin Magento 2.4+ 

namespace Noonpayments\Noonpg\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod {

    const PAYMENT_NOON_CODE = 'noonpg';    

    protected $_code = self::PAYMENT_NOON_CODE;

	protected $_isInitializeNeeded = true;
	
	/**
    * @var \Magento\Framework\Exception\LocalizedExceptionFactory
    */
    protected $_exception;

    /**
    * @var \Magento\Sales\Api\TransactionRepositoryInterface
    */
    protected $_transactionRepository;

    /**
    * @var Transaction\BuilderInterface
    */
    protected $_transactionBuilder;

    /**
    * @var \Magento\Framework\UrlInterface
    */
    protected $_urlBuilder;

    /**
    * @var \Magento\Sales\Model\OrderFactory
    */
    protected $_orderFactory;
	protected $_countryHelper;
    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $_storeManager;
	
	protected $_logger;
	
	protected $adnlinfo;
	protected $title;
	
	//protected $formKey;
    /**
     * 
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Sales\Model\OrderFactory $orderFactory,
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
      public function __construct(
      \Magento\Framework\UrlInterface $urlBuilder,
      \Magento\Framework\Exception\LocalizedExceptionFactory $exception,
      \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
      \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
      \Magento\Sales\Model\OrderFactory $orderFactory,
      \Magento\Store\Model\StoreManagerInterface $storeManager,
      \Magento\Framework\Model\Context $context,
      \Magento\Framework\Registry $registry,
      \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
      \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
      \Magento\Payment\Helper\Data $paymentData,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Payment\Model\Method\Logger $logger,
      \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
      \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
      array $data = []
    ) {
      $this->_urlBuilder = $urlBuilder;
      $this->_exception = $exception;
      $this->_transactionRepository = $transactionRepository;
      $this->_transactionBuilder = $transactionBuilder;
      $this->_orderFactory = $orderFactory;
      $this->_storeManager = $storeManager;
	  $this->_countryHelper = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Directory\Model\Country');      
	  $this->_logger = $logger;
	  
	  parent::__construct(
          $context,
          $registry,
          $extensionFactory,
          $customAttributeFactory,
          $paymentData,
          $scopeConfig,
          $logger,
          $resource,
          $resourceCollection,
          $data
      );
	}
	
	 /**
     * Instantiate state and set it to state object.
     *
     * @param string                        $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     */
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);		
		
        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

	public function getSuccessUrl($storeId = null)
    {
        return $this->_getUrl('checkout/onepage/success', $storeId);
    }

	public function getReturnUrl($storeId = null)
    {
        return $this->_getUrl('noonpg/checkout/callback', $storeId, false);
    }
	
	public function getCancelUrl($storeId = null)
    {
        return $this->_getUrl('checkout/onepage/failure', $storeId);
    }
	
	protected function _getUrl($path, $storeId, $secure = null)
    {
        $store = $this->_storeManager->getStore($storeId);

        return $this->_urlBuilder->getUrl(
            $path,
            ['_store' => $store, '_secure' => $secure === null ? $store->isCurrentlySecure() : $secure]
        );
    }
    

    /**
     * Return url according to environment
     * @return string
     */
    public function getCgiUrl() {     
        return $this->getConfigData('gatewayurl');
    }
	
	
    public function buildCheckoutRequest($order, $storeId = null) {
        $params['error']="";
		$params['data']="";
		$productInfo = "";	
		$action = '';
		$jsscript = '';		
		$orderReference = '';
		
		$items = $order->getAllItems();
		foreach ($items as $item) {
			if ($item->getSku() != "")
				$productInfo .= $item->getItemId().',';						
		}
        if ($productInfo != "") {
			$productInfo = rtrim($productInfo, ',');
			if(strlen($productInfo) > 50)
				$productInfo = substr($productInfo,0,50);
		}
		else 
			$productInfo = "Product Info";
	  	   			
		$postValues =  array();
		$orderValues = array();
		$confiValue = array();
		$postValues['apiOperation'] = 'INITIATE';
		$orderValues['name'] = $productInfo;
		$orderValues['channel'] = 'web';
		$orderValues['reference'] = $order->getIncrementId();
		$orderValues['amount'] = number_format((float) $order->getGrandTotal(), 2, '.', '');
		$orderValues['currency'] = $order->getOrderCurrencyCode();
		$orderValues['category'] = $this->getConfigData('orderroute');

		//$this->_logger->error(json_encode($orderValues));
		
		$confiValue['locale'] = $this->getConfigData('language');
		$confiValue['paymentAction'] = $this->getConfigData('paymentaction');
		$confiValue['returnUrl'] = $this->getReturnUrl();
		if(!Empty($this->getConfigData('styleprofile')))
			$confiValue['styleProfile'] = $this->getConfigData('styleprofile');
	
		$postValues['order'] = $orderValues;
		$postValues['configuration'] = $confiValue;
		
		$postJson = json_encode($postValues);
						
		$headerField = 'Key_Live';
		$credential_key = $this->getConfigData('businessidentifier').".".$this->getConfigData('appidentifier').":".$this->getConfigData('authkey');
		$headerValue = base64_encode($credential_key);

		if ($this->getConfigData('sandbox') == '1')
		{
			$headerField = 'Key_Test';				
		}

		$header = array();
		$header[] = 'Content-type: application/json';
		$header[] = 'Authorization: '.$headerField.' '.$headerValue;

		//$this->_logger->error("Post Values-".json_encode($postValues));	
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->getConfigData('gatewayurl'));
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSLVERSION, 6);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_ENCODING, '');		
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postJson);
		$response = curl_exec($curl);
		$curlerr = curl_error($curl);
		//$this->_logger->error("CURL Response-".json_encode($response));
		//$this->_logger->error("CURL Error-".json_encode($curlerr));
		if ($curlerr != '')
		{
			$params['error'] = $curlerr;
			
		}
		else {
			$res = json_decode($response);
			if (isset($res->resultCode) && $res->resultCode == 0 && 
				isset($res->result->checkoutData->postUrl) && isset($res->result->order->id))
			{
				$action = $res->result->checkoutData->postUrl;
				$jsscript = $res->result->checkoutData->jsUrl;
				$orderReference = $res->result->order->id;
				if (empty($action) || empty($jsscript) || empty($orderReference))
					$params['error'] = 'Payment Action could not be initiated. Verify credentials/checkout info.';							
			}
			else
				$params['error'] = 'Gateway did not return any response. Contact Administrator.';							
		}			
		
		if($params['error'] == "") {
		if ($this->getConfigData('operatingmode') == 'redirect')
		{
			$params['data'] = "<form action=\"" . $action . "\" method=\"post\" id=\"paynoon_form\" name=\"paynoon_form\">
						<button style='display:none' id='submit_noonpay_payment_form' name='submit_noonpay_payment_form'>Pay Now</button>
					</form>
					<script type=\"text/javascript\">document.getElementById(\"paynoon_form\").submit();</script>";
		}
		else
		{
			$params['data'] = "<script type='text/javascript' src=" . $jsscript . "></script>
					<script type='text/javascript'>
					function noonResponseCallBack(data) 
					{
						var returnurl= '".$this->getReturnUrl()."';
						if (data && data != null)
							window.location.href = returnurl + '?merchantReference=' + data.merchantReference + '&orderId=' + data.orderId + '&paymentType=' + data.paymentType;			
					}				
					function noonDoPayment()
					{
							var settings ={	Checkout_Method: 1, SecureVerification_Method: 1,	Call_Back: noonResponseCallBack, Frame_Id: 'noonPaymentFrame'	};
							var tries = 0;
							var noonTimer = setInterval(() => {
								if (typeof ProcessCheckoutPayment == 'function') { 
									clearInterval(noonTimer)
									ProcessCheckoutPayment(settings);
								}
								else
								{						
									if (++tries > 20)
									{
										clearInterval(noonTimer);
										alert('Failed to contact payment gateway. Please try again.');
									}
								}
							}, 500);			
				
						return false;
					}			
					noonDoPayment();				
					</script>";
		}
		}
        return $params;
    }

    //validate response
    public function validateResponse($order,$returnParams) {
		$order_id = $order->getIncrementId();
		
		try {
			$url = $this->getConfigData('gatewayurl').'/'.$returnParams['orderId'];
			//$this->_logger->error(json_encode($url));
			$headerField = 'Key_Live';
			$headerValue = base64_encode($this->getConfigData('businessidentifier').".".$this->getConfigData('appidentifier').":".$this->getConfigData('authkey'));
	
			if ($this->getConfigData('sandbox') == '1')
			{
				$headerField = 'Key_Test';				
			}
			$header = array();
			$header[] = 'Content-type: application/json';
			$header[] = 'Authorization: '.$headerField.' '.$headerValue;
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSLVERSION, 6);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_ENCODING, '');
			curl_setopt($curl, CURLOPT_TIMEOUT, 60);
				
			$response = curl_exec($curl);
			$curlerr = curl_error($curl);		
			//$this->_logger->error(json_encode($response));
			
			if ($curlerr != '')
				return false;
			else {
				$res = json_decode($response);
				if (isset($res->resultCode) && $res->resultCode == 0)
				{
					if (isset($res->result->transactions[0]->status) && $res->result->transactions[0]->status == 'SUCCESS') {
						if (isset($res->result->order->totalCapturedAmount) && isset($res->result->order->totalSalesAmount)
						&& isset($res->result->order->totalRemainingAmount) && isset($res->result->order->reference))
						{
							$capturedAmount = $res->result->order->totalCapturedAmount;
							$saleAmount = $res->result->order->totalSalesAmount;
							$txn_id_ret = $res->result->order->reference;
							$remainingAmount = $res->result->order->totalRemainingAmount;
							$orderAmount =  number_format((float) $order->getGrandTotal(), 2, '.', '');

							if ($this->getConfigData('paymentaction') == "SALE" && $orderAmount == $saleAmount && $capturedAmount >= $orderAmount && $txn_id_ret == $order_id) {								
								return true;								
							}
							else if ($this->getConfigData('paymentaction') == "AUTHORIZE" && $orderAmount == $remainingAmount && $txn_id_ret == $order_id) {								
								return true;								
							} else {
								return false;
							}
						}
					}
				}					
			}
			return false;
		} catch (Exception $e) {
			return false;
		}		     
    }

    public function postProcessing(\Magento\Sales\Model\Order $order,
            \Magento\Framework\DataObject $payment, $response) {
        
		$flag = false;
		
		if($this->validateResponse($order,$response))
		{
			$message='PAYMENT CAPTURED::';
			if ($this->getConfigData('paymentaction') == "AUTHORIZE") 
				$message = 'PAYMENT AUTHORIZED::';
			
			$payment->setTransactionId($response['orderId'])       
				->setPreparedMessage($message)
				->setShouldCloseParentTransaction(true)
				->setIsTransactionClosed(0)							
				->registerCaptureNotification(number_format((float) $order->getGrandTotal(), 2, '.', ''),true);			
			
			//$this->logger->error($res);			
			
			$order->setTotalPaid($order->getGrandTotal()); 		
			$order->setState(Order::STATE_PROCESSING)->setStatus(Order::STATE_PROCESSING);
			$order->save();
			//$invoice = $payment->getCreatedInvoice();
			$flag=true;
		}
		
        /* Uncomment this code if mail is configured
		if ($invoice && !$order->getEmailSent()) {
            $this->_orderSender->send($order);
            $order->addStatusHistoryComment(
                __('You notified customer about invoice #%1.', $invoice->getIncrementId())
            )->setIsCustomerNotified(
                true
            )->save();
        }*/
		return $flag;
    }	
}
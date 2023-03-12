<?php
// noon payments v2.1.1...
//noon payment plugin Magento 2.4+

namespace Sigma\NoonPaymentsGraphQl\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedExceptionFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;

class PaymentMethod extends \Noonpayments\Noonpg\Model\PaymentMethod
{

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * PaymentMethod constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param LocalizedExceptionFactory $exception
     * @param TransactionRepositoryInterface $transactionRepository
     * @param BuilderInterface $transactionBuilder
     * @param OrderFactory $orderFactory
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param Logger $logger
     * @param OrderSender $orderSender
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        LocalizedExceptionFactory $exception,
        TransactionRepositoryInterface $transactionRepository,
        BuilderInterface $transactionBuilder,
        OrderFactory $orderFactory,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        Logger $logger,
        OrderSender $orderSender,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_logger = $logger;
        parent::__construct(
            $urlBuilder,
            $exception,
            $transactionRepository,
            $transactionBuilder,
            $orderFactory,
            $storeManager,
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection
        );
        $this->orderSender = $orderSender;
    }

    /**
     * @param $order
     * @param null $storeId
     * @return array
     */
    public function buildCheckoutRequest($order, $storeId = null)
    {
        $params['error'] = "";
        $params['data'] = "";
        $productInfo = "";
        $action = '';
        $jsscript = '';
        $orderReference = '';

        $items = $order->getAllItems();
        foreach ($items as $item) {
            if ($item->getSku() != "")
                $productInfo .= $item->getItemId() . ',';
        }
        if ($productInfo != "") {
            $productInfo = rtrim($productInfo, ',');
            if (strlen($productInfo) > 50)
                $productInfo = substr($productInfo, 0, 50);
        } else
            $productInfo = "Product Info";

        $postValues = array();
        $orderValues = array();
        $confiValue = array();
        $postValues['apiOperation'] = 'INITIATE';
        $orderValues['name'] = $productInfo;
        $orderValues['channel'] = 'web';
        $orderValues['reference'] = $order->getIncrementId();
        $orderValues['amount'] = number_format((float)$order->getGrandTotal(), 2, '.', '');
        $orderValues['currency'] = $order->getOrderCurrencyCode();
        $orderValues['category'] = $this->getConfigData('orderroute');

        $this->_logger->error(json_encode($orderValues));

        $confiValue['locale'] = $this->getConfigData('language');
        $confiValue['paymentAction'] = $this->getConfigData('paymentaction');
        $confiValue['returnUrl'] = $this->getReturnUrl();
        if (!empty($this->getConfigData('styleprofile')))
            $confiValue['styleProfile'] = $this->getConfigData('styleprofile');

        $postValues['order'] = $orderValues;
        $postValues['configuration'] = $confiValue;

        $postJson = json_encode($postValues);

        $headerField = 'Key_Live';
        $credential_key = $this->getConfigData('businessidentifier') . "." . $this->getConfigData('appidentifier') . ":" . $this->getConfigData('authkey');
        $headerValue = base64_encode($credential_key);

        if ($this->getConfigData('sandbox') == '1') {
            $headerField = 'Key_Test';
        }

        $header = array();
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: ' . $headerField . ' ' . $headerValue;

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
        if ($curlerr != '') {
            $params['error'] = $curlerr;

        } else {
            $res = json_decode($response);
            if (isset($res->resultCode) && $res->resultCode == 0 &&
                isset($res->result->checkoutData->postUrl) && isset($res->result->order->id)) {
                $action = $res->result->checkoutData->postUrl;
                $jsscript = $res->result->checkoutData->jsUrl;
                $orderReference = $res->result->order->id;
                if (empty($action) || empty($jsscript) || empty($orderReference))
                    $params['error'] = 'Payment Action could not be initiated. Verify credentials/checkout info.';
            } else
                $params['error'] = 'Gateway did not return any response. Contact Administrator.';
        }

        if ($params['error'] == "") {
            if ($this->getConfigData('operatingmode') == 'redirect') {
                $params['data'] = $action;
            } else {
                $params['data'] = "<script type='text/javascript' src=" . $jsscript . "></script>
                    <script type='text/javascript'>
                    function noonResponseCallBack(data)
                    {
                        var returnurl= '" . $this->getReturnUrl() . "';
                        if (data && data != null)
                            window.location.href = returnurl + '?merchantReference=' + data.merchantReference + '&orderId=' + data.orderId + '&paymentType=' + data.paymentType;
                    }
                    function noonDoPayment()
                    {
                            var settings ={ Checkout_Method: 1, SecureVerification_Method: 1,   Call_Back: noonResponseCallBack, Frame_Id: 'noonPaymentFrame'   };
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

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getReturnUrl($storeId = null)
    {
        return $this->_scopeConfig->getValue("payment/noonpg/returnurl", ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getStoreId());
    }

    /**
     * @param Order $order
     * @param DataObject $payment
     * @param $response
     * @return bool
     */
    public function postProcessing(Order $order, DataObject $payment, $response): bool
    {
        $flag = false;
        if ($this->validateResponse($order, $response)) {
            $message = 'PAYMENT CAPTURED::';
            if ($this->getConfigData('paymentaction') == "AUTHORIZE")
                $message = 'PAYMENT AUTHORIZED::';

            $payment->setTransactionId($response['orderId'])
                ->setPreparedMessage($message)
                ->setShouldCloseParentTransaction(true)
                ->setIsTransactionClosed(0)
                ->registerCaptureNotification(number_format((float)$order->getGrandTotal(), 2, '.', ''), true);

            $order->setTotalPaid($order->getGrandTotal());
            $order->setState(Order::STATE_PROCESSING)->setStatus(Order::STATE_PROCESSING);
            $order->save();
            $invoice = $payment->getCreatedInvoice();
            $flag = true;
            if ($invoice && !$order->getEmailSent()) {
                $this->orderSender->send($order);
                $order->addStatusHistoryComment(
                    __('You notified customer about invoice #%1.', $invoice->getIncrementId())
                )->setIsCustomerNotified(
                    true
                )->save();
            }
        }
        return $flag;
    }
}

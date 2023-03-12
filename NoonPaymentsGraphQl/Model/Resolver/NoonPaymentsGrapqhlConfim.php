<?php

namespace Sigma\NoonPaymentsGraphQl\Model\Resolver;

use Exception;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Sigma\NoonPaymentsGraphQl\Model\PaymentMethod;
use Psr\Log\LoggerInterface;

class NoonPaymentsGrapqhlConfim implements ResolverInterface
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;
    /**
     * @var GuestPaymentInformationManagementInterface
     */
    private $guestPaymentInformationManagementInterface;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var PaymentInformationManagementInterface
     */
    private $paymentInformationManagementInterface;
    /**
     * @var LoggerInterface
     */
    private $_logger;
    /**
     * @var OrderFactory
     */
    private $orderFactory;
    /**
     * @var OrderResourceInterface
     */
    private $orderResource;
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var OrderInterface
     */
    private $order;
    /**
     * @var PaymentMethod
     */
    private $noonPaymentMethod;
    /**
     * @var PaymentInterface
     */
    private $paymentInterface;
    private Cart $cart;

    /**
     * NoonPaymentsGrapqhlConfim constructor.
     * @param QuoteRepository $quoteRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param StoreManagerInterface $storeManager
     * @param GuestPaymentInformationManagementInterface $guestPaymentInformationManagementInterface
     * @param PaymentInformationManagementInterface $paymentInformationManagementInterface
     * @param LoggerInterface $logger
     * @param PaymentInterface $paymentInterface
     * @param PaymentMethod $noonPaymentMethod
     * @param OrderInterface $order
     * @param QuoteFactory $quoteFactory
     * @param OrderResourceInterface $orderResource
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        StoreManagerInterface $storeManager,
        GuestPaymentInformationManagementInterface $guestPaymentInformationManagementInterface,
        PaymentInformationManagementInterface $paymentInformationManagementInterface,
        LoggerInterface $logger,
        PaymentInterface $paymentInterface,
        PaymentMethod $noonPaymentMethod,
        OrderInterface $order,
        QuoteFactory $quoteFactory,
        OrderResourceInterface $orderResource,
        OrderFactory $orderFactory,
        Cart $cart
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->storeManager = $storeManager;
        $this->guestPaymentInformationManagementInterface = $guestPaymentInformationManagementInterface;
        $this->paymentInformationManagementInterface = $paymentInformationManagementInterface;
        $this->_logger = $logger;
        $this->paymentInterface = $paymentInterface;
        $this->noonPaymentMethod = $noonPaymentMethod;
        $this->order = $order;
        $this->quoteFactory = $quoteFactory;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->cart = $cart;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null)
    {
        $merchantReference = $args['merchantReference'];
        $paymentType = $args['paymentType'];
        $orderId = $args['orderId'];
        $result = [];
        $params = array("orderId" => $orderId, "merchantReference" => $merchantReference, "paymentType" => $paymentType);
        try {
            $orderModel = $this->orderFactory->create();
            $order = $orderModel->loadByIncrementId($merchantReference);
            $actualOrderId = $order->getId();
            $order = $this->order->load($actualOrderId);
            $quote = $this->quoteFactory->create()->load($order->getQuoteId());
            if ($this->noonPaymentMethod->validateResponse($order, $params)) {
                $payment = $order->getPayment();
                if ($this->noonPaymentMethod->postProcessing($order, $payment, $params)) {
                    $result['result'] = 'Payment Successful. Thank you for your order.';
                    $result['order_id'] = $merchantReference;
                    //$quote->setIsActive(0); // commented as this will create customer auto-logout issue...
                    //$quote->save();
                    $allItems = $quote->getAllVisibleItems();
                    foreach ($allItems as $item) {
                        $itemId = $item->getItemId();
                        $this->cart->removeItem($itemId)->save();
                    }
                    return $result;
                }
            } else {
                $result['result'] = 'Payment failed. Please try again or choose a different payment method';
                $result['order_id'] = '';
                return $result;
            }
            //$quote->setIsActive(0);
            //$quote->save();
        } catch (LocalizedException | Exception $e) {
            $result['result'] = $e->getMessage();
            $result['order_id'] = '';
        }
        return $result;
    }
}

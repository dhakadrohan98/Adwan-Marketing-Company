<?php
namespace Sigma\NoonPaymentsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

class NoonPaymentsGraphqlCreate implements ResolverInterface
{
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $guestPaymentInformationManagementInterface,
        \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagementInterface,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Api\Data\PaymentInterface $paymentInterface,
        \Sigma\NoonPaymentsGraphQl\Model\PaymentMethod $noonPaymentMethod,
        \Magento\Sales\Api\Data\OrderInterface $order
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->storeManager=$storeManager;
        $this->guestPaymentInformationManagementInterface = $guestPaymentInformationManagementInterface;
        $this->paymentInformationManagementInterface = $paymentInformationManagementInterface;
        $this->_logger = $logger;
        $this->paymentInterface = $paymentInterface;
        $this->noonPaymentMethod = $noonPaymentMethod;
        $this->order = $order;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null)
    {
        $cartMaskId = $args['cartMaskId'];
        $email = $args['email'];
        $customerIsGuest = $args['customerIsGuest'];
        $selctedPaymentMethod = $args['selectedPaymentMethod'];
        $result =[];
        if (!isset($cartMaskId) && !isset($email)) {
            throw new GraphQlInputException(__('Invalid parameter list.'));
        }
        $this->_logger->info("Masked Cart ID :" . print_r($cartMaskId, true));
        $this->_logger->info("Email ID :" . print_r($email, true));
        try {
            $paymentData = $this->paymentInterface;
            $paymentData->setMethod($selctedPaymentMethod);
            $cartId = $this->maskedQuoteIdToQuoteId->execute($cartMaskId);
            $quote = $this->quoteRepository->get($cartId);
            if($quote)
            {

                $billingAddress = $quote->getBillingAddress();
                if($customerIsGuest==1)
                {
                    $orderId=$this->guestPaymentInformationManagementInterface->savePaymentInformationAndPlaceOrder($cartMaskId,$email,$paymentData,$billingAddress);
                    $quote->setIsActive(1);
                    $quote->save();
                }
                else{
                    $orderId = $this->paymentInformationManagementInterface->savePaymentInformationAndPlaceOrder($cartId,$paymentData,$billingAddress);
                    $quote->setIsActive(1);
                    $quote->save();
                }
                $order = $this->order->load($orderId);
                $html = $this->noonPaymentMethod->buildCheckoutRequest($order);
                if(isset($html['error']) && $html['error']!="") {
                    $this->_logger->error("noonpg Error-".json_encode($html));
                  //  $this->messageManager->addError("<strong>Error:</strong> ".$html['error']);
                    $result['result'] = 'FAILED';
                    $result['redirect_url'] = '';
                    $result['error'] = $html['error'];
                    $this->_logger->info(" Failed" . print_r($result, true));
                    return $result;
                }
                else{
                    $result['result'] = 'SUCCESS';
                    $result['redirect_url'] = $html['data'];
                    $result['error'] = '';
                    $this->_logger->info(" Success" . print_r($result, true));
                    return $result;
                }
            }
            else{
                $result['result'] = 'FAILED';
                $result['redirect_url'] = '';
                $result['error'] = 'Failed to load quote';
                $this->_logger->info(" Failed" . print_r($result, true));
                return $result;
            }
        }
            catch(\Exception $e){
                echo $e;
            }
    }
}

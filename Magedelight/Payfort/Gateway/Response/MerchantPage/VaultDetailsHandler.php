<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_Payfort
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Payfort\Gateway\Response\MerchantPage;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Payment\Model\InfoInterface;

class VaultDetailsHandler implements HandlerInterface
{
    private static $ccMapper = ['VISA' => 'VI','MASTERCARD' => 'MC','AMEX' => 'AE'];
    /**
     * @var PaymentTokenInterfaceFactory
     */
    protected $paymentTokenFactory;
    /**
     * @var SubjectReader
     */
    private $subjectReader;
    
    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    protected $paymentExtensionFactory;
     /**
      * @var \Magento\Framework\Serialize\Serializer\Json
      */
    private $serializer;
    
    public function __construct(
        SubjectReader $subjectReader,
        PaymentTokenInterfaceFactory $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->subjectReader = $subjectReader;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }
    
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        // add vault payment token entity to extension attributes
        $paymentToken = $this->getVaultPaymentToken($payment, $response);
        if (null !== $paymentToken) {
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }
    }

    /**
     * Get vault payment token entity
     *
     * @param \Braintree\Transaction $transaction
     * @return PaymentTokenInterface|null
     */
    protected function getVaultPaymentToken(\Magento\Sales\Model\Order\Payment\Interceptor $payment, $response)
    {
        // Check token existing in gateway response
        $is_active_payment_token_enabler = $payment->getAdditionalInformation(
            'is_active_payment_token_enabler'
        );
        if (!$is_active_payment_token_enabler) {
            return null;
        }
        $token = $payment->getAdditionalInformation(
            \Magedelight\Payfort\Gateway\Response\MerchantPage\TokenHandler::PAYMENT_TOKEN
        );
        if (empty($token)) {
            return null;
        }

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setGatewayToken($token);
        $paymentToken->setExpiresAt($this->getExpirationDate($response));
        $last4cc = substr($response['card_number'], -4);
        $expiry_date = $response['expiry_date'];
        $exp_yr = substr($expiry_date, 0, 2);
        $exp_mt = substr($expiry_date, 2, 4);
        $expirationDate = $exp_mt . $exp_yr;
        $paymentToken->setTokenDetails($this->convertDetailsToJSON([
            'type' => $this->getCreditCardType($response['payment_option']),
            'maskedCC' => $last4cc,
            'expirationDate' => $expirationDate
        ]));

        return $paymentToken;
    }

    /**
     * @param Transaction $transaction
     * @return string
     */
    private function getExpirationDate($response)
    {
        $expiry_date = $response['expiry_date'];
        $exp_yr = substr($expiry_date, 0, 2);
        $exp_mt = substr($expiry_date, 2, 4);
        $expDate = new \DateTime(
            $exp_yr
            . '-'
            . $exp_mt
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new \DateTimeZone('UTC')
        );
        $expDate->add(new \DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    /**
     * Convert payment token details to JSON
     * @param array $details
     * @return string
     */
    private function convertDetailsToJSON($details)
    {
        $json = $this->serializer->serialize($details);
        return $json ? $json : '{}';
    }

    /**
     * Get type of credit card mapped from Braintree
     *
     * @param string $type
     * @return array
     */
    private function getCreditCardType($type)
    {
        $cc_type = (isset(self::$ccMapper[$type]))? self::$ccMapper[$type] :$type;
        return $cc_type;
    }

    /**
     * Get payment extension attributes
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }
}

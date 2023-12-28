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
namespace Magedelight\Payfort\Gateway\Helper;

use Magento\Framework\Math\Random;
use Magento\Framework\Encryption\EncryptorInterface;
use Magedelight\Payfort\Gateway\Config\Config as PayfortConfig;
use Magedelight\Payfort\Helper\Data as PayfortHelper;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magedelight\Payfort\Gateway\Request\RequestSaleCommandBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magedelight\Payfort\Gateway\Request\RequestRefundCommandBuilder;

/**
 * Class TokenVarifyRequest
 */
class TokenVarifyRequest
{
    const RANDOM_LENGTH = 8;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var PayfortConfig
     */
    private $payfortConfig;

    /**
     * @var PayfortHelper
     */
    private $payfortHelper;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * TokenVarifyRequest constructor.
     * @param Random $random
     * @param EncryptorInterface $encryptor
     * @param PayfortConfig $payfortConfig
     * @param PayfortHelper $payfortHelper
     * @param RemoteAddress $remoteAddress
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Random $random,
        EncryptorInterface $encryptor,
        PayfortConfig $payfortConfig,
        PayfortHelper $payfortHelper,
        RemoteAddress $remoteAddress,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession
    ) {
        $this->random = $random;
        $this->encryptor = $encryptor;
        $this->payfortConfig = $payfortConfig;
        $this->payfortHelper = $payfortHelper;
        $this->remoteAddress = $remoteAddress;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
    }

    /**
     * @param $response
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareCaptureRequest($response)
    {
        $customer = $this->customerSession->getCustomer();
        $merchant_reference = $this->random->getRandomString(self::RANDOM_LENGTH, Random::CHARS_DIGITS);
        $request['merchant_identifier'] = $this->encryptor->decrypt($this->payfortConfig->getMerchantIdentifier());
        $request['access_code'] = $this->encryptor->decrypt($this->payfortConfig->getAccessCode());
        $request['merchant_reference'] = $merchant_reference;
        $request['command'] = RequestSaleCommandBuilder::PURCHASE;
        $request['language'] = $this->payfortHelper->getCurrentStoreLanguageCode();
        $request['customer_name'] = $response['card_holder_name'];
        $request['customer_email'] = $customer->getEmail();
        $request['customer_ip'] = $this->remoteAddress->getRemoteAddress();
        $request['remember_me'] = "YES";
        $cardType = $this->getCardType($response['card_bin']);
        if ($cardType != "MADA") {
            $request['check_3ds'] = "NO";
        }
        $request['amount'] = "10";
        $request['currency'] = $this->storeManager->getStore()->getBaseCurrencyCode();
        $request['token_name'] = $response['token_name'];
        $signFields = $request;
        $shaString  = '';
        ksort($signFields);
        foreach ($signFields as $k => $v) {
            $shaString .= "$k=$v";
        }
        $shaRequestPhrase = $this->encryptor->decrypt($this->payfortConfig->getRequestSha());
        $shaString = $shaRequestPhrase . $shaString . $shaRequestPhrase;
        $shaType = $this->payfortConfig->getShaType();
        $signature = hash($shaType, $shaString);
        $request['signature'] = $signature;
        return $request;
    }

    /**
     * @param $response
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareRefundRequest($response)
    {
        $customer = $this->customerSession->getCustomer();
        $merchant_reference = $this->random->getRandomString(self::RANDOM_LENGTH, Random::CHARS_DIGITS);
        $request['merchant_identifier'] = $this->encryptor->decrypt($this->payfortConfig->getMerchantIdentifier());
        $request['access_code'] = $this->encryptor->decrypt($this->payfortConfig->getAccessCode());
        //$request['merchant_reference'] = $merchant_reference;
        $request['merchant_reference'] = $response['merchant_reference'];
        $request['command'] = RequestRefundCommandBuilder::REFUND;
        $request['language'] = $this->payfortHelper->getCurrentStoreLanguageCode();
        $request['amount'] = "10";
        $request['currency'] = $this->storeManager->getStore()->getBaseCurrencyCode();
        $request['fort_id'] = $response['fort_id'];
        $signFields = $request;
        $shaString  = '';
        ksort($signFields);
        foreach ($signFields as $k => $v) {
            $shaString .= "$k=$v";
        }
        $shaRequestPhrase = $this->encryptor->decrypt($this->payfortConfig->getRequestSha());
        $shaString = $shaRequestPhrase . $shaString . $shaRequestPhrase;
        $shaType = $this->payfortConfig->getShaType();
        $signature = hash($shaType, $shaString);
        $request['signature'] = $signature;
        return $request;
    }

    /**
     * @param $str
     * @param string $format
     * @return bool|int|string
     */
    private function getCardType($str, $format = 'string')
    {
        if (empty($str)) {
            return false;
        }

        $matchingPatterns = [
            'MADA' => '/^52/',
            'VI' => '/^4[0-9]{0,}$/',
            'MC' => '/^(5[1-5]|222[1-9]|22[3-9]|2[3-6]|27[01]|2720)[0-9]{0,}$/',
            'AE' => '/^3[47][0-9]{0,}$/'

        ];

        $ctr = 1;
        foreach ($matchingPatterns as $key => $pattern) {
            if (preg_match($pattern, $str)) {
                return $format == 'string' ? $key : $ctr;
            }
            $ctr++;
        }
    }
}

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
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Payfort\Gateway\Config;

use function Aws\boolean_value;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const PAYFORT_ACTIVE = 'active';
    const PAYFORT_VAULT_ACTIVE = 'payfort_cc_vault_active';
    const PAYFORT_TITLE = 'title';
    const PAYFORT_ACCESS_CODE = 'access_code';
    const PAYFORT_MERCH_ID = 'merchant_identifier';
    const PAYFORT_TEST = 'sandbox_flag';
    const PAYFORT_PAYMENT_ACTION = 'payment_action';
    const PAYFORT_DEBUG = 'debug';
    const PAYFORT_CCV = 'useccv';
    const PAYFORT_GATEWAY_URL = 'transaction_url';
    const PAYFORT_TEST_GATEWAY_URL = 'transaction_url_test_mode';
    const PAYFORT_VALIDATION_TYPE = 'validation_mode';
    const PAYFORT_NEW_ORDER_STATUS = 'order_status';
    const PAYFORT_VALIDATION_NONE = 'none';
    const PAYFORT_VALIDATION_TEST = 'testMode';
    const PAYFORT_VALIDATION_LIVE = 'liveMode';
    const REQUESTSHA = 'sha_in_pass_phrase';
    const RESPONSESHA = 'sha_out_pass_phrase';
    const SHATYPE = 'sha_type';
    const PAYFORT_TEST_CGI_URL = 'cgi_url_test_mode';
    const PAYFORT_CGI_URL = 'cgi_url';
    const THREEDSECURE = 'threedsecure';
    const ORDERTOKEN = 'order_token';
    const SAVEDPAYMENT = 'saved_payment';
    const DEVICE_FINGERPRINT_ACTIVE = 'device_fingerprint_active';
    const RECURRING = 'recurring';
    const TOKENIZATION = 'token';

    private $scopeConfig;

    public function getIsActive()
    {
        return $this->getValue(self::PAYFORT_ACTIVE);
    }

    public function getIsVaultActive()
    {
        return $this->getValue(self::PAYFORT_VAULT_ACTIVE);
    }

    /**
     * This method will return whether test mode is enabled or not.
     *
     * @return bool
     */
    public function getIsTestMode()
    {
         return $this->getValue(self::PAYFORT_TEST);
    }

     /**
      * This metod will return PAYFORT Gateway url depending on test mode enabled or not.
      *
      * @return string
      */
    public function getGatewayUrl()
    {
        $isTestMode = $this->getIsTestMode();
        $gatewayUrl = ($isTestMode) ? $this->getValue(self::PAYFORT_TEST_GATEWAY_URL) :
            $this->getValue(self::PAYFORT_GATEWAY_URL);
        return $gatewayUrl;
    }
     /**
      * This metod will return PAYFORT Gateway url depending on test mode enabled or not.
      *
      * @return string
      */
    public function getCgiUrl()
    {
        $isTestMode = $this->getIsTestMode();
        $gatewayUrl = ($isTestMode) ? $this->getValue(self::PAYFORT_TEST_CGI_URL) :
            $this->getValue(self::PAYFORT_CGI_URL);
        return $gatewayUrl;
    }

    /**
     * This methos will return Payfort payment method title set by admin to display
     * on onepage checkout payment step.
     *
     * @return string
     */
    public function getMethodTitle()
    {
        return (string) $this->getValue(self::PAYFORT_TITLE);
    }
    /**
     * This methos will return Payfort order token set by admin to display
     * on onepage checkout payment step.
     *
     * @return string
     */
    public function getOrderToken()
    {
        return (string) $this->getValue(self::ORDERTOKEN);
    }

    /**
     * This method will return merchant api login id set by admin in configuration.
     *
     * @return string
     */
    public function getAccessCode($storeid = null)
    {
      //  return $this->getValue(self::PAYFORT_ACCESS_CODE);
        return $this->getValue(self::PAYFORT_ACCESS_CODE, $storeid);
    }

    /**
     * This method will return merchant api transaction key set by admin in configuration.
     *
     * @return string
     */
    public function getMerchantIdentifier($storeid = null)
    {
        return $this->getValue(self::PAYFORT_MERCH_ID, $storeid);
    }

    /**
     * This will returne payment action whether it is authorized or authorize and capture.
     *
     * @return string
     */
    public function getPaymentAction()
    {
        return (string) $this->getValue(self::PAYFORT_PAYMENT_ACTION);
    }
    /**
     * This method will return whether debug is enabled from config.
     *
     * @return bool
     */
    public function getIsDebugEnabled()
    {
        return (boolean) $this->getValue(self::PAYFORT_DEBUG);
    }

    /**
     * This method return whether card verification is enabled or not.
     *
     * @return bool
     */
    public function isCardVerificationEnabled()
    {
        return (boolean) $this->getValue(self::PAYFORT_CCV);
    }
    /**
     * This method return whether threed secure enable or not.
     *
     * @return bool
     */
    public function getThreeDActive()
    {
        return (boolean) $this->getValue(self::THREEDSECURE);
    }

    /**
     * Payfort validation mode.
     *
     * @return string
     */
    public function getValidationMode()
    {
        return (string) $this->getValue(self::PAYFORT_VALIDATION_TYPE);
    }

    /**
     * Payfort request sha.
     *
     * @return string
     */
    public function getRequestSha($storeid = null)
    {
        return (string) $this->getValue(self::REQUESTSHA, $storeid);
    }
    /**
     * Payfort response sha.
     *
     * @return string
     */
    public function getResponseSha($storeid = null)
    {
        return (string) $this->getValue(self::RESPONSESHA, $storeid);
    }

    /**
     * @param null $storeid
     * @return string
     */
    public function getSavedPayment($storeid = null)
    {
        return (string) $this->getValue(self::SAVEDPAYMENT, $storeid);
    }

    /**
     * @param null $storeid
     * @return string
     */
    public function getDeviceFingerPrintActive($storeid = null)
    {
        return (boolean) $this->getValue(self::DEVICE_FINGERPRINT_ACTIVE, $storeid);
    }

    /**
     * Payfort response sha.
     *
     * @return string
     */
    public function getShaType($storeid = null)
    {
       // return (string) $this->getValue(self::SHATYPE);
        return (string) $this->getValue('sha_type', $storeid);
    }

    public function getDefaultFormat()
    {
        return $this->scopeConfig->getValue(
            'customer/address_templates/html',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}

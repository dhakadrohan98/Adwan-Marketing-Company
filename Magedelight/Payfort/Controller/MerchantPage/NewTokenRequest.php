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

namespace Magedelight\Payfort\Controller\MerchantPage;

use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magedelight\Payfort\Gateway\Config\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\Controller\Result\JsonFactory;
use Magedelight\Payfort\Helper\Data;

/**
 * Class TokenRequest
 * @package Magedelight\Payfort\Controller\MerchantPage
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewTokenRequest extends \Magento\Framework\App\Action\Action
{
    // const TOKENIZATION = 'CREATE_TOKEN';
    const TOKENIZATION = 'TOKENIZATION';

    const LANGUAGEVALUE = 'en';

    const LANGUAGE = 'language';

    const RETURNURL = 'payfort/MerchantPage/NewTokenResponse';

    const RANDOM_LENGTH = 8;

    const MERCHANTIDENTIFIER = 'merchant_identifier';

    const ACCESSCODE = 'access_code';

    const MERCHANT_REF = 'merchant_reference';

    const SERVICE_COMM = 'service_command';

    const RETURN_URL = 'return_url';

    const CURRENCY = 'currency';

    const CARDSECURITYCODE = 'card_security_code';

    const SIGNATURE = 'signature';

    /**
     * Unsigned fields
     *
     * @var array
     */
    private static $signatureFields = [
        self::MERCHANTIDENTIFIER,
        self::ACCESSCODE,
        self::MERCHANT_REF,
        self::SERVICE_COMM,
        self::LANGUAGE,
        self::RETURN_URL,
        self::CURRENCY,
        self::CARDSECURITYCODE
    ];
    private $SHARequestPhrase;
    private $SHAType;

    /**
     * @var Config
     */
    private $payfortConfig;

    /**
     * @var Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     *
     * @var type
     */
    private $urlBuilder;

    /**
     * @var Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * NewTokenRequest constructor.
     * @param Context $context
     * @param Config $config
     * @param EncryptorInterface $encryptor
     * @param FormFactory $formFactory
     * @param Random $random
     * @param StoreManagerInterface $storeManager
     * @param JsonFactory $resultJsonFactory
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        Config $config,
        EncryptorInterface $encryptor,
        FormFactory $formFactory,
        Random $random,
        StoreManagerInterface $storeManager,
        JsonFactory $resultJsonFactory,
        Data $dataHelper
    ) {
        parent::__construct($context);
        $this->payfortConfig = $config;
        $this->urlBuilder = $context->getUrl();
        $this->encryptor = $encryptor;
        $this->formFactory = $formFactory;
        $this->random = $random;
        $this->storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $signFields = [];
        $form = $this->formFactory->create();
        $merchant_reference = $this->random->getRandomString(self::RANDOM_LENGTH, Random::CHARS_DIGITS);
        $form->setAction($this->payfortConfig->getCgiUrl())
            ->setId('payfort_token_form')
            ->setName('payfort_token_form')
            ->setMethod('POST')
            ->setEnctype('application/json')
            ->setUseContainer(true);
        $postfield = [];
        $postfield['merchant_identifier'] = $this->encryptor->decrypt($this->payfortConfig->getMerchantIdentifier());
        $postfield['access_code'] = $this->encryptor->decrypt($this->payfortConfig->getAccessCode());
        $postfield['merchant_reference'] = $merchant_reference;
        $postfield['service_command'] = self::TOKENIZATION;
        $postfield['language'] = $this->dataHelper->getCurrentStoreLanguageCode();
        $postfield['return_url'] = $this->urlBuilder->getUrl(self::RETURNURL);
        $postfield['currency'] = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        foreach ($postfield as $field => $value) {
            if (in_array($field, self::$signatureFields)) {
                $signFields[$field] = $value;
                continue;
            }
        }
        $shaString = '';
        ksort($signFields);
        foreach ($signFields as $k => $v) {
            $shaString .= "$k=$v";
        }
        $this->SHARequestPhrase = $this->encryptor->decrypt($this->payfortConfig->getRequestSha());
        $shaString = $this->SHARequestPhrase . $shaString . $this->SHARequestPhrase;
        $this->SHAType = $this->payfortConfig->getShaType();
        $signature = hash($this->SHAType, $shaString);
        $postfield[self::SIGNATURE] = $signature;
        foreach ($postfield as $field => $value) {
            $form->addField($field, 'hidden', ['name' => $field, 'value' => $value]);
        }
        $html = $form->toHtml();
        $result = $this->resultJsonFactory->create();
        $result->setData(['result' => $html]);
        return $result;
    }
}

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
namespace Magedelight\Payfort\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magedelight\Payfort\Gateway\Config\Config;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class SignatureBuilder
 * @package Magedelight\Payfort\Gateway\Request
 */
class SignatureBuilder implements BuilderInterface
{
    const MERCHANTIDENTIFIER = 'merchant_identifier';

    const ACCESSCODE = 'access_code';

    const MERCHANT_REF = 'merchant_reference';
    
    const SERVICE_COMM = 'service_command';
    
    const COMMAND = 'command';

    const LANGUAGE = 'language';

    const RETURN_URL = 'return_url';

    const CUSTOMER_IP = 'customer_ip';

    const AMOUNT = 'amount';

    const CURRENCY = 'currency';

    const CUSTOMER_EMAIL = 'customer_email';

    const CUSTOMER_NAME = 'customer_name';

    const TOKEN_NAME = 'token_name';
    
    const CHECK3D = 'check_3ds';
    
    const REMEMBERME = 'remember_me';
    
    const CARDHOLDERNAME = 'card_holder_name';
    
    const CARDSECURITYCODE = 'card_security_code';
    
    const FORTID = 'fort_id';

    const SIGNATURE = 'signature';
    const DEVICEFINGERPRINT = 'device_fingerprint';
    
    const ECI = 'eci';
    
    const PAYMENTOPTION = 'payment_option';

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
        self::COMMAND,
        self::LANGUAGE,
        self::RETURN_URL,
        self::CUSTOMER_IP,
        self::AMOUNT,
        self::CURRENCY,
        self::CUSTOMER_EMAIL,
        self::CUSTOMER_NAME,
        self::TOKEN_NAME,
        self::CHECK3D,
        self::REMEMBERME,
        self::CARDSECURITYCODE,
        self::FORTID,
        self::DEVICEFINGERPRINT,
        self::ECI
 //       self::CARDHOLDERNAME
    ];
    private $SHARequestPhrase;
    private $SHAType;


    /**
     * @var Config
     */
    private $payfortConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
      * @var BuilderInterface[] | TMap
      */
    private $builders;

    /**
     * SignatureBuilder constructor.
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     * @param SubjectReader $subjectReader
     * @param UrlInterface $urlBuilder
     * @param array $builders
     * @param TMapFactory $tmapFactory
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        SubjectReader $subjectReader,
        UrlInterface $urlBuilder,
        array $builders,
        TMapFactory $tmapFactory
    ) {
        $this->payfortConfig = $config;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->subjectReader = $subjectReader;
        $this->urlBuilder = $urlBuilder;
        $this->builders = $tmapFactory->create(
            [
                'array' => $builders,
                'type' => 'Magento\Payment\Gateway\Request\BuilderInterface'
            ]
        );
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $storeid = 0;
        if ($paymentDO->getOrder()!=null) {
            $order = $paymentDO->getOrder();
            $storeid = $order->getStoreId();
        }
        $signFields = [];
        $result = [];
        foreach ($this->builders as $builder) {
            // @TODO implement exceptions catching
            $result = array_merge($result, $builder->build($buildSubject));
        }

        foreach ($result as $field => $value) {
            if (in_array($field, self::$signatureFields)) {
                $signFields[$field] = $value;
                continue;
            }
        }
        $shaString             = '';
        ksort($signFields);
        foreach ($signFields as $k => $v) {
            $shaString .= "$k=$v";
        }
        $this->SHARequestPhrase = $this->encryptor->decrypt($this->payfortConfig->getRequestSha($storeid));
        $shaString = $this->SHARequestPhrase . $shaString . $this->SHARequestPhrase;
        $this->SHAType = $this->payfortConfig->getShaType($storeid);
        $signature = hash($this->SHAType, $shaString);
        $result[self::SIGNATURE] =  $signature;
        return $result;
    }
}

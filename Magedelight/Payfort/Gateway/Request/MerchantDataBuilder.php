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
use Magento\Framework\Math\Random;
use Magento\Framework\UrlInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class MerchantDataBuilder
 * @package Magedelight\Payfort\Gateway\Request
 */
class MerchantDataBuilder implements BuilderInterface
{
    const RANDOM_LENGTH = 8;
   
    /**
     * @var Config
     */
    private $payfortConfig;

    /**
     * @var Random
     */
    private $random;

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
     * MerchantDataBuilder constructor.
     * @param Config $config
     * @param Random $random
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     * @param SubjectReader $subjectReader
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Config $config,
        Random $random,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        SubjectReader $subjectReader,
        UrlInterface $urlBuilder
    ) {
        $this->payfortConfig = $config;
        $this->random = $random;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->subjectReader = $subjectReader;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        
        $orderIncrId  = $paymentDO->getOrder()->getId() .'_'.$this->random->getRandomString(self::RANDOM_LENGTH, Random::CHARS_DIGITS);
        $payment = $paymentDO->getPayment();
        $merchant_ref = $payment
            ->getAdditionalInformation(
                SignatureBuilder::MERCHANT_REF
            );
        if ($merchant_ref!=null) {
            $orderIncrId = $merchant_ref;
        }
        $storeid = 0;
        if ($paymentDO->getOrder()!=null) {
            $order = $paymentDO->getOrder();
            $storeid = $order->getStoreId();
        }
        $result = [
            'merchant_identifier' => $this->encryptor->decrypt($this->payfortConfig->getMerchantIdentifier($storeid)),
            'access_code' =>  $this->encryptor->decrypt($this->payfortConfig->getAccessCode($storeid)),
            'merchant_reference' => $orderIncrId
        ];
        return $result;
    }
}

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
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magedelight\Payfort\Helper\Data as PayfortHelper;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class CustomerInfoVaultBuilder
 * @package Magedelight\Payfort\Gateway\Request
 */
class CustomerInfoVaultBuilder implements BuilderInterface
{
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
     *
     * @var RemoteAddress
     */
    private $_remoteAddress;

    /**
     * @var PayfortHelper
     */
    private $payfortHelper;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $tokenManagement;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * CustomerInfoVaultBuilder constructor.
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     * @param SubjectReader $subjectReader
     * @param RemoteAddress $remoteAddress
     * @param PayfortHelper $payfortHelper
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param Json $serializer
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        SubjectReader $subjectReader,
        RemoteAddress $remoteAddress,
        PayfortHelper $payfortHelper,
        PaymentTokenManagementInterface $tokenManagement,
        Json $serializer
    ) {
        $this->payfortConfig = $config;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->subjectReader = $subjectReader;
        $this->_remoteAddress = $remoteAddress;
        $this->payfortHelper = $payfortHelper;
        $this->tokenManagement = $tokenManagement;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();
        $result = [
            'customer_name' =>  $billingAddress->getFirstname() . $billingAddress->getLastname(),
            'customer_email' =>  $billingAddress->getEmail(),
            'customer_ip' =>  $this->_remoteAddress->getRemoteAddress(),
            'remember_me' =>  'YES',
        ];
        if (!$this->checkCardIsMada($paymentDO)) {
            if ($this->payfortConfig->getSavedPayment()!=Config::RECURRING) {
                if (!($this->payfortConfig->getThreeDActive()) || ($this->payfortHelper->checkAdmin())) {
                    $result['check_3ds'] =  'NO';
                }
            }
        }

        return $result;
    }

    public function checkCardIsMada($paymentDO)
    {

        $additionalInformation = $paymentDO->getPayment()->getAdditionalInformation();

        if (isset($additionalInformation['card_type'])) {
            if ($additionalInformation['card_type'] == "MADA") {
                return true;
            }
        } elseif (isset($additionalInformation['customer_id']) && isset($additionalInformation['public_hash'])) {
            $paymentToken = $this->tokenManagement->getByPublicHash($additionalInformation['public_hash'], $additionalInformation['customer_id']);
            $tokenDetails = $this->serializer->unserialize($paymentToken->getDetails());
            if (isset($tokenDetails['type']) && $tokenDetails['type'] == "MADA") {
                return true;
            }
        }

        return false;
    }
}

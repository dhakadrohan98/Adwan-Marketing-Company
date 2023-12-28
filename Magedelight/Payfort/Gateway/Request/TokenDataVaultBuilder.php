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
use Magedelight\Payfort\Observer\DataAssignObserver;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Registry;

/**
 * Class TokenDataVaultBuilder
 * @package Magedelight\Payfort\Gateway\Request
 */
class TokenDataVaultBuilder implements BuilderInterface
{
    const PUBLICHASH = 'public_hash';
    const CVV = 'cvv';
    const CUSTOMERID = 'customer_id';
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
     * @var PaymentTokenManagementInterface
     */
    private $tokenManagement;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * TokenDataVaultBuilder constructor.
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     * @param SubjectReader $subjectReader
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param Registry $registry
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        SubjectReader $subjectReader,
        PaymentTokenManagementInterface $tokenManagement,
        Registry $registry
    ) {
        $this->payfortConfig = $config;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->subjectReader = $subjectReader;
        $this->tokenManagement = $tokenManagement;
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $publicHash = $paymentDO->getPayment()
                            ->getAdditionalInformation(
                                self::PUBLICHASH
                            );
        $cvv = $this->registry->registry(DataAssignObserver::CVV);
        $this->registry->unregister(DataAssignObserver::CVV);
        $customerId = $paymentDO->getPayment()
                            ->getAdditionalInformation(
                                self::CUSTOMERID
                            );
        $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $customerId);
        if (!$paymentToken) {
            throw new \Exception('No available payment tokens');
        }
        $token_name = $paymentToken->getGatewayToken();
         $result = [
            'token_name' =>  $token_name
         ];
        if($this->payfortConfig->getSavedPayment()==Config::RECURRING){
            $result['eci'] = 'RECURRING';
        }
        else{
           if ($cvv!='') {
             $result['card_security_code'] = $cvv;
           }
        }
        return $result;
    }
}

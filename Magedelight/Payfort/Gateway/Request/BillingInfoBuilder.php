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
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class MerchantDefineDataBuilder
 */
class BillingInfoBuilder implements BuilderInterface
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
     * MerchantDefineDataBuilder constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        SubjectReader $subjectReader
    ) {
        $this->payfortConfig = $config;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->subjectReader = $subjectReader;
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
            'card_holder_name' =>  $billingAddress->getFirstname() ." ". $billingAddress->getLastname(),
        ];
        return $result;
    }
}
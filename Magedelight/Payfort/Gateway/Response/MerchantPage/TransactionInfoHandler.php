<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
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
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Vault\Model\PaymentTokenFactory;

class TransactionInfoHandler implements HandlerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var PaymentTokenFactory
     */
    private $paymentCardSaveTokenFactory;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        EncryptorInterface $encryptor,
        PaymentTokenFactory $paymentCardSaveTokenFactory
    ) {
        $this->config = $config;
        $this->encryptor = $encryptor;
        $this->paymentCardSaveTokenFactory = $paymentCardSaveTokenFactory;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $fieldsToStore = explode(',', $this->config->getValue('paymentInfoKeys'));
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        foreach ($fieldsToStore as $field) {
            $requestFieldName = null;
            if (isset($response[$field])) {
                $requestFieldName = $field;
            }
            if (!$requestFieldName) {
                continue;
            }
            $paymentDO->getPayment()->setAdditionalInformation(
                $field,
                $response[$requestFieldName]
            );
        }
    }
}

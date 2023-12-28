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

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Encryption\EncryptorInterface;

class TokenHandler implements HandlerInterface
{
    const PAYMENT_TOKEN = 'token_name';
     
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * TokenHandler constructor.
     * @param SubjectReader $subjectReader
     * @param DateTimeFactory $dateTimeFactory
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        SubjectReader $subjectReader,
        DateTimeFactory $dateTimeFactory,
        EncryptorInterface $encryptor
    ) {
        $this->subjectReader = $subjectReader;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->encryptor = $encryptor;
    }
    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws \InvalidArgumentException
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($response[self::PAYMENT_TOKEN])) {
            return;
        }
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $paymentDO->getPayment()
            ->setAdditionalInformation(
                self::PAYMENT_TOKEN,
                $response[self::PAYMENT_TOKEN]
            );
    }
}

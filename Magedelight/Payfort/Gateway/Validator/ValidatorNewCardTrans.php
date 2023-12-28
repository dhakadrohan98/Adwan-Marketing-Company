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
namespace Magedelight\Payfort\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magedelight\Payfort\Gateway\Config\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magedelight\Payfort\Gateway\Helper\ConvertAmount;

class ValidatorNewCardTrans extends AbstractValidator
{

    const RESPONSE_CODE = 'response_code';

    /**
     *  Successful transaction.
     */
    const RESPONSE_AUTH_SUCCESSFUL = '02000';
    const RESPONSE_SALE_SUCCESSFUL = '14000';
    const RESPONSE_REFUND_SUCCESSFUL = '06000';
    const RESPONSE_CAPTURE_SUCCESSFUL = '04000';
    const RESPONSE_VOID_SUCCESSFUL = '08000';
    const RESPONSE_3D_SUCCESSFUL = '20064';
   
    private static $acceptableResponseCode = [
        self::RESPONSE_AUTH_SUCCESSFUL,
        self::RESPONSE_SALE_SUCCESSFUL,
        self::RESPONSE_REFUND_SUCCESSFUL,
        self::RESPONSE_CAPTURE_SUCCESSFUL,
        self::RESPONSE_VOID_SUCCESSFUL
    ];

    /**
     * @var Config
     */
    private $payfortConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var ConvertAmount
     */
    private $payfortHelper;

    /**
     * ValidatorTrans constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param Config $config
     * @param EncryptorInterface $encryptor
     * @param ConvertAmount $payfortHelper
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        Config $config,
        EncryptorInterface $encryptor,
        ConvertAmount $payfortHelper
    ) {
        $this->payfortConfig = $config;
        $this->encryptor = $encryptor;
        $this->payfortHelper = $payfortHelper;
        parent::__construct($resultFactory);
    }
    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return null|ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        /* signature validation logic */
        $success = false;
        $responseSignature = $response['signature'];
        unset($response['r']);
        unset($response['signature']);
        unset($response['integration_type']);
        unset($response['form_key']);
        $signFields = $response;
        if (isset($signFields['amount'])) {
            $amount = $this->payfortHelper->convertFortAmount($signFields['amount'], $signFields['currency']);
            $signFields['amount'] = $amount;
        }
        $shaString = '';
        ksort($signFields);
        foreach ($signFields as $k => $v) {
            $shaString .= "$k=$v";
        }
        $this->SHAResponsePhrase = $this->encryptor->decrypt($this->payfortConfig->getResponseSha());
        $shaString = $this->SHAResponsePhrase . $shaString . $this->SHAResponsePhrase;
        $this->SHAType = $this->payfortConfig->getShaType();
        $signature = hash($this->SHAType, $shaString);
        if ($signature==$responseSignature) {
            $success = true;
        }
        /* end signature validation logic */
        $result = $this->createResult(
            (in_array(
                $response[static::RESPONSE_CODE],
                self::$acceptableResponseCode
            ) && $success),
            [__('Your payment has been declined. Please try again.')]
        );
        return $result;
    }
}

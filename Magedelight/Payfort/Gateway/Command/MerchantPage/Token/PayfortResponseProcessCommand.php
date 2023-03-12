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
namespace Magedelight\Payfort\Gateway\Command\MerchantPage\Token;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;

/**
 * Class PayfortResponseProcessCommand
 * @package Magedelight\Payfort\Gateway\Command\MerchantPage\Token
 */
class PayfortResponseProcessCommand implements CommandInterface
{
    /**
     * @var ValidatorInterface
     */
    private $payfortvalidator;

    /**
     * @var Logger
     */
    private $payfortlogger;

    /**
     * @var HandlerInterface
     */
    private $handlerInterface;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentManagement;

    /**
     * PayfortResponseProcessCommand constructor.
     * @param ValidatorInterface $payfortvalidator
     * @param Logger $logger
     * @param HandlerInterface $handler
     * @param PaymentMethodManagementInterface $paymentManagement
     */
    public function __construct(
        ValidatorInterface $payfortvalidator,
        Logger $logger,
        HandlerInterface $handler,
        PaymentMethodManagementInterface $paymentManagement
    ) {
        $this->handlerInterface = $handler;
        $this->payfortlogger = $logger;
        $this->payfortvalidator = $payfortvalidator;
        $this->paymentManagement = $paymentManagement;
    }

    public function execute(array $commandSubject)
    {
        $response = SubjectReader::readResponse($commandSubject);
        $this->payfortlogger->debug(['payment_payforttoken_response' => $response]);
        $result = $this->payfortvalidator->validate($commandSubject);
        if (!$result->isValid()) {
            throw new \LogicException();
        }
        $this->handlerInterface->handle($commandSubject, $response);
        $paymentDO = SubjectReader::readPayment($commandSubject);
        $this->paymentManagement->set(
            $paymentDO->getOrder()->getId(),
            $paymentDO->getPayment()
        );
    }
}

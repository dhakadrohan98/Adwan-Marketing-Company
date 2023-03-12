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

/**
 * Class PayfortCreateCommand
 * @package Magedelight\Payfort\Gateway\Command\MerchantPage\Token
 */
class PayfortCreateCommand implements CommandInterface
{
    private $arrayResultFactory;
    private $builder;
    private $payfortlogger;

    public function __construct(
        \Magento\Payment\Gateway\Command\Result\ArrayResultFactory $arrayResultFactory,
        \Magento\Payment\Gateway\Request\BuilderInterface $builder,
        \Magento\Payment\Model\Method\Logger $logger
    ) {
        $this->arrayResultFactory = $arrayResultFactory;
        $this->builder = $builder;
        $this->payfortlogger = $logger;
    }

    public function execute(array $commandSubject)
    {
        $result = $this->builder->build($commandSubject);
        $this->payfortlogger->debug(['payment_payforttoken_request' => $result]);
        return $this->arrayResultFactory->create(['array' => $result]);
    }
}

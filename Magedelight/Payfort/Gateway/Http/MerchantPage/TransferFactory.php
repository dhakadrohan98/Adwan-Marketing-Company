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
namespace Magedelight\Payfort\Gateway\Http\MerchantPage;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
          return $this->transferBuilder
            ->setHeaders(['Content-Type: application/json'])
            ->setBody($request)
            ->setMethod(\Zend_Http_Client::POST)
            ->setUri(
                (bool)$this->config->getValue('sandbox_flag')
                ? $this->config->getValue('transaction_url_test_mode')
                : $this->config->getValue('transaction_url')
            )
            ->shouldEncode(false)
            ->build();
    }
}

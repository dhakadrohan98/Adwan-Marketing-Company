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

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Framework\ObjectManager\TMapFactory;

class PayfortHandler implements HandlerInterface
{
    /**
     *
     * @param array $handlers
     * @param \Magedelight\Payfort\Gateway\Response\MerchantPage\TMapFactory $tmapFactory
     */
    public function __construct(
        array $handlers,
        TMapFactory $tmapFactory
    ) {
        $this->handlers = $tmapFactory->create(
            [
                'array' => $handlers,
                'type' => 'Magento\Payment\Gateway\Response\HandlerInterface'
            ]
        );
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
        if ($response['response_code']!=\Magedelight\Payfort\Gateway\Validator\ValidatorTrans::RESPONSE_3D_SUCCESSFUL) {
            foreach ($this->handlers as $handler) {
                // @TODO implement exceptions catching
                $handler->handle($handlingSubject, $response);
            }
        }
    }
}

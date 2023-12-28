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
namespace Magedelight\Payfort\Controller\MerchantPage;

use Magedelight\Payfort\Gateway\Command\MerchantPage\Token\CreateCommand;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManager;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Quote\Api\PaymentMethodManagementInterface;

/**
 * Class TokenRequest
 * @package Magedelight\Payfort\Controller\MerchantPage
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TokenRequest extends \Magento\Framework\App\Action\Action
{
    const TOKEN_COMMAND_NAME = 'TokenCreateCommand';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var SessionManager
     */
    private $checkoutSession;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @param Context $context
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param ConfigInterface $config
     * @param SessionManager $checkoutSession
     * @param JsonFactory $jsonFactory
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     */
    public function __construct(
        Context $context,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        ConfigInterface $config,
        SessionManager $checkoutSession,
        JsonFactory $jsonFactory,
        PaymentMethodManagementInterface $paymentMethodManagement
    ) {
        parent::__construct($context);
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->jsonFactory = $jsonFactory;
        $this->config = $config;
        $this->paymentMethodManagement = $paymentMethodManagement;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $arguments = [
            'amount' => 0,
            'cc_type' => (string)$this->getRequest()->getParam('cc_type')
        ];

        $result = [];
        try {
            /** @var CreateCommand $command */
            $command = $this->commandPool->get(self::TOKEN_COMMAND_NAME);

            if (!$this->checkoutSession->getQuote()) {
                throw new \Exception;
            }

            $payment = $this->paymentMethodManagement->get(
                $this->checkoutSession->getQuote()->getId()
            );

            $arguments['payment'] = $this->paymentDataObjectFactory->create($payment);

            $commandResult = $command->execute($arguments);

            $result[\Magedelight\Payfort\Model\Ui\ConfigProvider::CODE]['fields'] = $commandResult->get();
            $result['success'] = true;
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['success'] = false;
            $result['error_messages'] = __('Payment Token Build Error.');
        }

        $jsonResult = $this->jsonFactory->create();
        $jsonResult->setData($result);

        return $jsonResult;
    }
}

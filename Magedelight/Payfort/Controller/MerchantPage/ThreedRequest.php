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
use Magedelight\Payfort\Observer\DataAssignObserver;
use Magento\Customer\Model\Session as CustomerSession;
/**
 * Class TokenRequest
 * @package Magedelight\Payfort\Controller\MerchantPage
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ThreedRequest extends \Magento\Framework\App\Action\Action
{
    const THREEDCHECKAUTHCARD = 'ThreeDCheckAuthorize';
    const THREEDCHECKCAPCARD = 'ThreeDCheckCapture';
    const THREEDCHECKAUTHVAULT = 'ThreeDCheckVaultAuthorize';
    const THREEDCHECKCAPVAULT = 'ThreeDCheckVaultCapture';

    const ORDER_AMN = 'ord_amn';

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
     *
     * @var Magedelight\Payfort\Gateway\Config\Config
     */
    private $payfortConfig;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var \Magento\Quote\Api\Data\PaymentInterface
     */
    private $paymentdata;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * ThreedRequest constructor.
     * @param Context $context
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param ConfigInterface $config
     * @param SessionManager $checkoutSession
     * @param JsonFactory $jsonFactory
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CustomerSession $customerSession
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentdata
     * @param \Magento\Framework\Registry $registry
     * @param \Magedelight\Payfort\Gateway\Config\Config $payfortConfig
     */
    public function __construct(
        Context $context,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        ConfigInterface $config,
        SessionManager $checkoutSession,
        JsonFactory $jsonFactory,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CustomerSession $customerSession,
        \Magento\Quote\Api\Data\PaymentInterface $paymentdata,
        \Magento\Framework\Registry $registry,
        \Magedelight\Payfort\Gateway\Config\Config $payfortConfig
    ) {
        parent::__construct($context);
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->jsonFactory = $jsonFactory;
        $this->config = $config;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->customerSession = $customerSession;
        $this->paymentdata = $paymentdata;
        $this->registry = $registry;
        $this->payfortConfig = $payfortConfig;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = [];
        try {
            /** @var CreateCommand $command */
            //           $command = $this->commandPool->get(self::THREEDCHECKAUTHCARD);
            $command = self::THREEDCHECKCAPCARD;
            $paymentAction = $this->payfortConfig->getPaymentAction();

            if($paymentAction==\Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE){
                $command = self::THREEDCHECKAUTHCARD;
            }

            if (!$this->checkoutSession->getQuote()) {
                throw new \Exception;
            }
            $this->checkoutSession->setThreeDSecureData('');
            $paymentdata = $this->getRequest()->getParam('paymentdata');

            $payment = $this->paymentMethodManagement->get(
                $this->checkoutSession->getQuote()->getId()
            );

            if($payment==null)
            {
                $this->paymentdata->setMethod($paymentdata['method']);
                $this->paymentdata->setAdditionalData($paymentdata['additional_data']);
                $this->paymentMethodManagement->set(
                    $this->checkoutSession->getQuote()->getId(),$this->paymentdata
                );
                $payment = $this->paymentMethodManagement->get(
                    $this->checkoutSession->getQuote()->getId()
                );

            }
            if(isset($paymentdata['method'])){
                $payment->setMethod($paymentdata['method']);
            }
            if(strpos($payment->getMethod(), \Magedelight\Payfort\Model\Ui\ConfigProvider::CC_VAULT_CODE) !== false){
                $payment->setMethod(\Magedelight\Payfort\Model\Ui\ConfigProvider::CC_VAULT_CODE);
                $paymentdata = $this->getRequest()->getParam('paymentdata');

                $additionalData = $paymentdata['additional_data'];
                if(isset($additionalData[DataAssignObserver::CVV])) {
                    $this->registry->unregister(DataAssignObserver::CVV);
                    $this->registry->register(DataAssignObserver::CVV, $additionalData[DataAssignObserver::CVV]);
                }

                if($paymentAction==\Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE){
                    $command = self::THREEDCHECKAUTHVAULT;
                }
                else{
                    $command = self::THREEDCHECKCAPVAULT;
                }
                $payment->setAdditionalInformation(
                    'public_hash',
                    $paymentdata['additional_data']['public_hash']
                );

                $payment->setAdditionalInformation(
                    'customer_id',
                    $this->customerSession->getCustomer()->getId()
                );
            }
            $payment
                ->setAdditionalInformation(
                    self::ORDER_AMN,
                    $this->checkoutSession->getQuote()->getBaseGrandTotal()
                );

            $command = $this->commandPool->get($command);
            $arguments['payment'] = $this->paymentDataObjectFactory->create($payment);
            $commandResult = $command->execute($arguments);
            $result[\Magedelight\Payfort\Model\Ui\ConfigProvider::CODE] = $commandResult->get();
            $result['success'] = true;
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['success'] = false;
            // $result['error_messages'] = __('Payment Token Build Error.');
            $result['error_messages'] = $e->getMessage();
        }
        $jsonResult = $this->jsonFactory->create();
        $jsonResult->setData($result);

        return $jsonResult;
    }
}

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

use Magedelight\Payfort\Gateway\Command\MerchantPage\Token\ResponseProcessCommand;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Payment\Block\Transparent\Iframe;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;

/**
 * Class TokenRequest
 * @package Magedelight\Payfort\Controller\MerchantPage
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TokenResponse extends \Magento\Framework\App\Action\Action
{
    const TOKEN_COMMAND_NAME = 'TokenProcessCommand';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customer;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $_encryptor;

    /**
     * @var \Magento\Vault\Model\PaymentTokenFactory
     */
    private $paymentCardSaveTokenFactory;

    /**
     * TokenResponse constructor.
     * @param Context $context
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param LayoutFactory $layoutFactory
     * @param Registry $registry
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CartRepositoryInterface $cartRepository
     * @param \Magento\Customer\Model\Session $customer
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Vault\Model\PaymentTokenFactory $paymentCardSaveTokenFactory
     */
    public function __construct(
        Context $context,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        LayoutFactory $layoutFactory,
        Registry $registry,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CartRepositoryInterface $cartRepository,
        \Magento\Customer\Model\Session $customer,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Vault\Model\PaymentTokenFactory $paymentCardSaveTokenFactory
    ) {
        parent::__construct($context);
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->layoutFactory = $layoutFactory;
        $this->registry = $registry;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartRepository = $cartRepository;
        $this->_customer = $customer;
        $this->_encryptor = $encryptor;
        $this->paymentCardSaveTokenFactory = $paymentCardSaveTokenFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $request = $this->getRequest();
        $area =  $request->getParam('area');
        $response = $request->getParams();
        try {
            $arguments['response'] = $response;
            $merchantref = $response['merchant_reference'];
            $merchantrefArr = explode('_', $merchantref);
            $orderid = $merchantrefArr[0];
            $activeCart = $this->cartRepository->get(
                (int)$orderid
            );

            $payment = $this->paymentMethodManagement->get($activeCart->getId());

            $cardType = $this->getCardType($response['card_bin']);
            $payment->setAdditionalInformation("card_type", $cardType);
            if ($cardType == "MADA") {
                $result['avoid_treedsecure_check'] = true;
            }

            /** @var ResponseProcessCommand $command */
            $command = $this->commandPool->get(self::TOKEN_COMMAND_NAME);
            $arguments['payment'] = $this->paymentDataObjectFactory->create($payment);

            $command->execute($arguments);
            $result['success'] = true;
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['error_msg'] = __('Your payment has been declined. Please try again.');
        }

        $this->registry->register(Iframe::REGISTRY_KEY, $result);

        $resultLayout = $this->layoutFactory->create();
        $resultLayout->addDefaultHandle();
        switch ($area) {
            case 'adminhtml':
                $resultLayout
                        ->getLayout()
                        ->getUpdate()
                        ->load(['payfort_merchantpage_tokenresponse_adminhtml']);
                break;
            default:
                $resultLayout
                        ->getLayout()
                        ->getUpdate()
                        ->load(['payfort_merchantpage_tokenresponse']);
                break;
        }

        return $resultLayout;
    }
    
    
    private function getCardType($str, $format = 'string')
    {
        if (empty($str)) {
            return false;
        }

        $matchingPatterns = [
            'MADA' => '/^52/',
            'VI' => '/^4[0-9]{0,}$/',
            'MC' => '/^(5[1-5]|222[1-9]|22[3-9]|2[3-6]|27[01]|2720)[0-9]{0,}$/',
            'AE' => '/^3[47][0-9]{0,}$/'

        ];

        $ctr = 1;
        foreach ($matchingPatterns as $key => $pattern) {
            if (preg_match($pattern, $str)) {
                return $format == 'string' ? $key : $ctr;
            }
            $ctr++;
        }
    }
}

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

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManager;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Api\CartManagementInterface;

/**
 * Class TokenRequest
 * @package Magedelight\Payfort\Controller\MerchantPage
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ThreedResponse extends \Magento\Framework\App\Action\Action
{
    const TRANS_COMMAND_NAME = 'authorize';
    /**
     *
     * @var Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $order;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     *
     * @var Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;
    protected $scopeConfig;
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
     * @var CartManagementInterface
     */
    private $cartManagement;
    /**
     *
     * @param Context $context
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param ConfigInterface $config
     * @param SessionManager $checkoutSession
     * @param JsonFactory $jsonFactory
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CartManagementInterface $cartManagement
     * @param \Magento\Sales\Api\OrderRepositoryInterface $order
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        ConfigInterface $config,
        SessionManager $checkoutSession,
        JsonFactory $jsonFactory,
        PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $order,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->jsonFactory = $jsonFactory;
        $this->config = $config;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartManagement = $cartManagement;
        $this->order = $order;
        $this->orderSender = $orderSender;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        try {
            $request = $this->getRequest();
            $response = $request->getParams();
            if(count($response)<=1){
                return $this->resultRedirectFactory->create()->setPath('checkout/cart/');
            }
            /* store related customization */
            $storeId = 1;
            if (isset($response['language'])) {
                $reqlang = $response['language'];
                $storeManagerDataList = $this->storeManager->getStores();
                foreach ($storeManagerDataList as $value) {
                    $storeData = $this->storeManager->getStore($value['store_id']);
                    $haystack = $this->scopeConfig->getValue(
                        \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeData['code']
                    );
                    $langcode = strstr($haystack, '_', true);
                    if ($langcode == $reqlang) {
                        $storeId = $storeData['store_id'];
                        break;
                    }
                }
                
                $this->storeManager->setCurrentStore($storeId);
            }
            /* end store related customization */
            $this->checkoutSession->setThreeDSecureData($response);
            $quote = $this->checkoutSession->getQuote();
            if ($quote->getCustomerEmail() == null) {
                $quote->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
            }
            $orderId = $this->cartManagement->placeOrder($quote->getId());
            if ($orderId) {
                $order = $this->order->get($orderId);
                $this->orderSender->send($order);
                return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success/', ['_scope' => $storeId]);
            } else {
                return $this->resultRedirectFactory->create()->setPath('checkout/cart/', ['_scope' => $storeId]);
            }
        } catch (\Exception $e) {
            
             /* store related customization */
            $storeId = 1;
            if (isset($response['language'])) {
                $reqlang = $response['language'];
                $storeManagerDataList = $this->storeManager->getStores();
                foreach ($storeManagerDataList as $value) {
                    $storeData = $this->storeManager->getStore($value['store_id']);
                    $haystack = $this->scopeConfig->getValue(
                        \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeData['code']
                    );
                    $langcode = strstr($haystack, '_', true);
                    if ($langcode == $reqlang) {
                        $storeId = $storeData['store_id'];
                        break;
                    }
                }
                
        
                $this->storeManager->setCurrentStore($storeId);
            }
            /* end store related customization */
            $message = $response['response_message'];
            if ($message == 'success') {
                $message = __("Something went wrong.");
            }
           
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('checkout/cart/', ['_scope' => $storeId]);
        }
    }
}

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
namespace Magedelight\Payfort\Block\Customer;

use Magento\Store\Model\StoreManagerInterface;

class Edit extends \Magento\Framework\View\Element\Template
{
     /**
      *
      * @var Magento\Payment\Model\Config
      */
    protected $paymentConfig;
    
    /**
     *
     * @var Magedelight\Payfort\Gateway\Config\Config
     */
    protected $getconfig;

    /**
     *
     * @var Magento\Vault\Model\PaymentTokenFactory
     */
    protected $paymentCardSaveTokenFactory;
    
    /**
     *
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;
    
    /**
     *
     * @var Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepositoryInterface;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magedelight\Payfort\Gateway\Config\Config $getconfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magedelight\Payfort\Gateway\Config\Config $getconfig,
        \Magento\Vault\Model\PaymentTokenFactory $paymentCardSaveTokenFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->paymentConfig = $paymentConfig;
        $this->getconfig = $getconfig;
        $this->paymentCardSaveTokenFactory = $paymentCardSaveTokenFactory;
        $this->_customerSession = $customerSession;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->addressRepositoryInterface = $addressRepositoryInterface;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    public function getBackUrl()
    {
        return $this->urlBuilder->getUrl('vault/cards/listaction/');
    }
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getCard()
    {
        $public_hash = $this->getRequest()->getPostValue('public_hash');
        $customerId = $this->_customerSession->getCustomerId();
        if (!empty($public_hash)) {
            $cardDetails =  $this->paymentCardSaveTokenFactory->create()->getCollection()
                                ->addFieldToFilter('public_hash', ["eq" => $public_hash])
                                ->addFieldToFilter('customer_id', ["eq" => $customerId]);
            return $cardDetails->getData()[0];
        } else {
            return;
        }
    }

    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] = __('Month');
            $months = array_merge($months, $this->paymentConfig->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (!($years)) {
            $years = $this->paymentConfig->getYears();
            $years = [0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }

    public function hasVerification()
    {
        return (boolean)$this->getConfig('payment/md_payfort/useccv');
    }
    
    public function getCustomerBillingAddress()
    {
        
        $customer_id =  $this->_customerSession->getCustomer()->getId();
        $customer = $this->customerRepositoryInterface->getById($customer_id);
        $billingAddressId = $customer->getDefaultBilling();
        $carDetail = [];
        $carDetail['firstname'] = '';
        $carDetail['lastname'] = '';
        if ($billingAddressId) {
            $billingAddress = $this->addressRepositoryInterface->getById($billingAddressId);
            $carDetail['firstname'] = $billingAddress->getFirstName();
            $carDetail['lastname'] = $billingAddress->getLastName();
        }
        return $carDetail;
    }

    public function getBaseCurrencyCode()
    {
        return $this->storeManager->getStore()->getBaseCurrencyCode();
    }
}

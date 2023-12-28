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

class Addcard extends \Magento\Framework\View\Element\Template
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
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->paymentConfig = $paymentConfig;
        $this->getconfig = $getconfig;
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
    public function getPostUrl()
    {
        return $this->urlBuilder->getUrl('payfort/addcard/edit');
    }

    public function getBaseCurrencyCode()
    {
        return $this->storeManager->getStore()->getBaseCurrencyCode();
    }
}

<?php
/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
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
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Payfort\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_locale;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\Resolver $locale
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\Resolver $locale
    ) {
        $this->_storeManager = $storeManager;
        $this->_locale = $locale;
        parent::__construct($context);
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->getConfig('payment/md_payfort/active');
    }

    /**
     * @return string|array|int|boolean
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function checkAdmin()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $app_state = $om->get('Magento\Framework\App\State');
        $area_code = $app_state->getAreaCode();
        if ($app_state->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return true;
        } else {
            return false;
        }
    }

    public function getCurrentStoreLanguageCode()
    {
        $haystack  = $this->_locale->getLocale();
        $langCode = strstr($haystack, '_', true);
        return $langCode;
    }
}

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
namespace Magedelight\Payfort\Block;

use Magento\Payment\Helper\Data;
use Magento\Framework\App\ObjectManager;

class Form extends \Magento\Payment\Block\Adminhtml\Transparent\Form
{
     /**
      * @var Data
      */
    private $paymentDataHelper;
   /**
    * Check if vault enabled
    * @return bool
    */
    public function isVaultEnabled()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $vaultPayment = $this->getVaultPayment();
        return $vaultPayment->isActive($storeId);
    }

     /**
      * Get configured vault payment for Payfort
      * @return VaultPaymentInterface
      */
    private function getVaultPayment()
    {
        return $this->getPaymentDataHelper()->getMethodInstance('md_payfort_cc_vault');
    }

    /**
     * Get payment data helper instance
     * @return Data
     * @deprecated
     */
    private function getPaymentDataHelper()
    {
        if ($this->paymentDataHelper === null) {
            $this->paymentDataHelper = ObjectManager::getInstance()->get(Data::class);
        }
        return $this->paymentDataHelper;
    }
}

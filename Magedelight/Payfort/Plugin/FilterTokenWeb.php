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
namespace Magedelight\Payfort\Plugin;

use Magedelight\Payfort\Model\VaultwebFactory;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Store\Model\StoreManagerInterface;

class FilterTokenWeb
{
    /**
     * @var VaultwebFactory
     */
    private $vaultwebFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        VaultwebFactory $vaultwebFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->vaultwebFactory = $vaultwebFactory;
        $this->storeManager = $storeManager;
    }
    public function afterGetVisibleAvailableTokens(PaymentTokenManagement $subject, $result)
    {
        $vaultwebModel = $this->vaultwebFactory->create();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $vaultData = $vaultwebModel->getCollection()
                                   ->addFieldToFilter('website_id', $websiteId)
                                   ->getData();
        $vaultData = array_column($vaultData, 'vault_token');
        $webItems = [];
        foreach ($result as $i => $item) {
            $methodcode = $item->getPaymentMethodCode();
            if ($methodcode=='md_payfort') {
                if (in_array($item->getGatewayToken(), $vaultData)) {
                    $webItems[$i] = $item;
                }
            } else {
                $webItems[$i] = $item;
            }
        }
        return $webItems;
    }
}

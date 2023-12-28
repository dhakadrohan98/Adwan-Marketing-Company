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
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Payfort\Gateway\Response\MerchantPage;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class UpdateVault implements HandlerInterface
{
    /**
     * @var Magento\Vault\Model\PaymentTokenFactory
     */
    protected $paymentTokenFactory;
    /**
     * @var SubjectReader
     */
    private $subjectReader;
    
    
    
    public function __construct(
        SubjectReader $subjectReader,
        \Magento\Vault\Model\PaymentTokenFactory $paymentTokenFactory
    ) {
        $this->subjectReader = $subjectReader;
        $this->paymentTokenFactory = $paymentTokenFactory;
    }
    
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $publichash = $payment->getAdditionalInformation(
            \Magedelight\Payfort\Gateway\Request\TokenDataVaultBuilder::PUBLICHASH
        );
        $customerId = $payment->getAdditionalInformation(
            \Magedelight\Payfort\Gateway\Request\TokenDataVaultBuilder::CUSTOMERID
        );
        $vaultColl = $this->paymentTokenFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('public_hash', $publichash)
                    ->addFieldToFilter('customer_id', $customerId);
        $vaultid = $vaultColl->getFirstItem()->getId();
        $vaultModel = $this->paymentTokenFactory->create()->load($vaultid);
        $vaultdetails = json_decode($vaultModel->getDetails(), true);
        $vaultdetails['firstuse'] = 0;
        $vaultModel->setDetails(json_encode($vaultdetails));
        $vaultModel->save();
    }
}

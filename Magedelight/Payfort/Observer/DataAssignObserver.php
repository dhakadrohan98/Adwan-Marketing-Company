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
namespace Magedelight\Payfort\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\Registry;
use Magedelight\Payfort\Gateway\Config\Config as PayfortConfig;

/**
 * Class DataAssignObserver
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    const CVV = 'cvv';
    const DEVICEFINGERPRINT = 'device_fingerprint';
  
    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::CVV
    ];

    /**
     *
     * @var \Magento\Framework\Registry 
     */
    private $registry;

    /**
     * @var PayfortConfig
     */
    private $payfortConfig;

    /**
     * DataAssignObserver constructor.
     * @param Registry $registry
     * @param PayfortConfig $payfortConfig
     */
    public function __construct(
        Registry $registry,
        PayfortConfig $payfortConfig
        )
    {
        $this->registry = $registry;
        $this->payfortConfig = $payfortConfig;
    }
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }
        if (isset($additionalData[self::CVV])) {
            $this->registry->register(self::CVV, $additionalData[self::CVV]);
        }
        if($this->payfortConfig->getDeviceFingerPrintActive()){
            if(isset($additionalData[self::DEVICEFINGERPRINT]))
            {
                $payment = $this->readPaymentModelArgument($observer);
                $additionalInformation = $payment->getAdditionalInformation();
                $additionalInformation[self::DEVICEFINGERPRINT] = $additionalData[self::DEVICEFINGERPRINT];
                $payment->setAdditionalInformation($additionalInformation);
            }
        }

    }
}

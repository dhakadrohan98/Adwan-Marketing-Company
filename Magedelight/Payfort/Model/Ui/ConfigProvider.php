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
namespace Magedelight\Payfort\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magedelight\Payfort\Gateway\Config\Config;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Payment\Model\CcConfig as CcConfig;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'md_payfort';

    const CC_VAULT_CODE = 'md_payfort_cc_vault';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PaymentConfig
     */
    private $paymentConfig;

    /**
     * @var CcConfig
     */
    private $ccConfig;

    /**
     * ConfigProvider constructor.
     * @param Config $config
     * @param PaymentConfig $paymentConfig
     * @param CcConfig $ccConfig
     */
    public function __construct(
        Config $config,
        PaymentConfig $paymentConfig,
        CcConfig $ccConfig
    ) {
        $this->config = $config;
        $this->paymentConfig = $paymentConfig;
        $this->ccConfig = $ccConfig;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $madaAsset = $this->ccConfig->createAsset('Magedelight_Payfort::images/mada.png');
        $meezaAsset = $this->ccConfig->createAsset('Magedelight_Payfort::images/meeza.png');
        list($width, $height) = getimagesize($madaAsset->getSourceFile());
        return [
            'payment' => [
                self::CODE => [
                    'isActive' => $this->config->getIsActive(),
                    'useCvv' => $this->config->isCardVerificationEnabled(),
                    'environment' => $this->config->getIsTestMode(),
                    'ccVaultCode' => self::CC_VAULT_CODE,
                    'threedacitve' => $this->config->getThreeDActive(),
                    'savedPaymentMethod' => $this->config->getSavedPayment()

                ],
                'ccform' => [
                    'icons' => [
                        'MADA' => [
                            'height'=> $height,
                            'title'=> "MADA",
                            'url' => $madaAsset->getUrl(),
                            'width'=> $width

                        ],
                        'MEEZA' => [
                            'height'=> $height,
                            'title'=> "MEEZA",
                            'url' => $meezaAsset->getUrl(),
                            'width'=> $width

                        ]
                    ]

                ]
            ]
        ];
    }
}

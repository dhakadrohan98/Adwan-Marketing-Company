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

namespace Magedelight\Payfort\Block\Customer;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;
use Magento\Payment\Model\CcConfig as CcConfig;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\CcConfigProvider;

class CardRenderer extends AbstractCardRenderer
{
    /**
     * @var CcConfigProvider
     */
    private $iconsProvider;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param CcConfigProvider $iconsProvider
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CcConfigProvider $iconsProvider,
        CcConfig $ccConfig,
        array $data = []
    ) {
        $this->ccConfig = $ccConfig;
        parent::__construct($context, $iconsProvider, $data);
    }

    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === 'md_payfort';
    }

    /**
     * @return string
     */
    public function getNumberLast4Digits()
    {
        return $this->getTokenDetails()['maskedCC'];
    }

    /**
     * @return string
     */
    public function getExpDate()
    {
        return $this->getTokenDetails()['expirationDate'];
    }

    /**
     * @return string
     */
    public function getIconUrl()
    {
        
        return $this->getPayfortIconForType($this->getTokenDetails()['type'])['url'];
    }

    /**
     * @return int
     */
    public function getIconHeight()
    {
        return $this->getPayfortIconForType($this->getTokenDetails()['type'])['height'];
    }

    /**
     * @return int
     */
    public function getIconWidth()
    {
        return $this->getPayfortIconForType($this->getTokenDetails()['type'])['width'];
    }

    public function getPayfortIconForType($type)
    {
        if ($type=='MADA' || $type=='MEEZA') {
            return $this->getPayfortIcons()[$type];
        } else {
            return $this->getIconForType($type);
        }
    }
    private function getPayfortIcons()
    {
        $madaAsset = $this->ccConfig->createAsset('Magedelight_Payfort::images/mada.png');
        $meezaAsset = $this->ccConfig->createAsset('Magedelight_Payfort::images/meeza.png');
        list($width, $height) = getimagesize($madaAsset->getSourceFile());
        return [
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
        ];
    }

    public function getMethodCode()
    {
         $token = $this->getToken();
         return $token->getPaymentMethodCode();
    }
}

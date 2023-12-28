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
namespace Magedelight\Payfort\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magedelight\Payfort\Gateway\Config\Config;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class MerchantDefineDataBuilder
 */
class ReturnUrlBuilder implements BuilderInterface
{
    const RETURNURL = 'payfort/MerchantPage/TokenResponse/area/';

    /**
     * @var Config
     */
    private $payfortConfig;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ScopeInterface
     */
    private $scope;

    /**
     * @var \Magento\Framework\Url
     */
    private $urlHelper;

    /**
     * ReturnUrlBuilder constructor.
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     * @param SubjectReader $subjectReader
     * @param UrlInterface $urlBuilder
     * @param ScopeInterface $scope
     * @param \Magento\Framework\Url $urlHelper
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        SubjectReader $subjectReader,
        UrlInterface $urlBuilder,
        ScopeInterface $scope,
        \Magento\Framework\Url $urlHelper
    ) {
        $this->payfortConfig = $config;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->subjectReader = $subjectReader;
        $this->urlBuilder = $urlBuilder;
        $this->scope = $scope;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(array $buildSubject)
    {
        $path = self::RETURNURL . $this->scope->getCurrentScope();
        $url =  $this->urlHelper->getUrl($path);
        $result = [
            'return_url' => $url
        ];
        return $result;
    }
}

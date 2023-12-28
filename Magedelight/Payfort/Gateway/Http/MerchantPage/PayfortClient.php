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
namespace Magedelight\Payfort\Gateway\Http\MerchantPage;

use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\Session\SessionManager;
use Magedelight\Payfort\Gateway\Helper\ConvertAmount;

class PayfortClient implements ClientInterface
{
   /**
    * @var ZendClientFactory
    */
    private $clientFactory;

    /**
     * @var ConverterInterface | null
     */
    private $converter;

    /**
     * @var Logger
     */
    private $logger;
    
      /**
       *
       * @var ConvertAmount
       */
    private $converamtHelper;

     /**
      * @var SessionManager
      */
    private $checkoutSession;
    /**
     * @param ZendClientFactory $clientFactory
     * @param Logger $logger
     * @param ConverterInterface | null $converter
     */
    public function __construct(
        ZendClientFactory $clientFactory,
        Logger $logger,
        ConvertAmount $converamtHelper,
        SessionManager $checkoutSession,
        ConverterInterface $converter = null
    ) {
        $this->clientFactory = $clientFactory;
        $this->converter = $converter;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->converamtHelper = $converamtHelper;
    }

    /**
     * {inheritdoc}
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $this->logger->debug(['request' => $transferObject->getBody()]);
        if ($this->checkoutSession->getThreeDSecureData()!='') {
            $array_result = $this->checkoutSession->getThreeDSecureData();
            $this->checkoutSession->setThreeDSecureData('');
        } else {
            $ch = curl_init();
            $useragent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0";
            curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json;charset=UTF-8']);
            curl_setopt($ch, CURLOPT_URL, $transferObject->getUri());
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_ENCODING, "compress, gzip");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // allow redirects
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); // The number of seconds to wait while trying to connect
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($transferObject->getBody()));
            $response = curl_exec($ch);
            curl_close($ch);
            $array_result = json_decode($response, true);
        }
        $this->logger->debug(['response' => $array_result]);
        if (empty($array_result)) {
            return false;
        }
        if (isset($array_result['amount']) && isset($array_result['currency'])) {
            $amn = $array_result['amount'];
            $currency = $array_result['currency'];
            $amount = $this->converamtHelper->castAmountFromFort($amn, $currency);
            $array_result['amount'] = $amount;
        }
        return $array_result;
    }
}

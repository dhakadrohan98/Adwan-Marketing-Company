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
namespace Magedelight\Payfort\Controller\MerchantPage;

use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magedelight\Payfort\Gateway\Config\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Vault\Model\PaymentTokenFactory;
use Magedelight\Payfort\Model\VaultwebFactory;
use Magedelight\Payfort\Gateway\Http\MerchantPage\TransferFactory;
use Magedelight\Payfort\Gateway\Helper\TokenVarifyRequest;
use Magedelight\Payfort\Gateway\Http\MerchantPage\PayfortClient;
use Magedelight\Payfort\Gateway\Validator\ValidatorNewCardTrans;

/**
 * Class TokenRequest
 * @package Magedelight\Payfort\Controller\MerchantPage
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateTokenResponse extends \Magento\Framework\App\Action\Action
{
    const TOKENSUCCESS = 18000;

    /**
     * @var Config
     */
    private $payfortConfig;
     
    /**
     * @var Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;
    
    /**
     *
     * @var type
     */
    private $urlBuilder;

    /**
     * @var Magento\Framework\Data\FormFactory
     */
    private $formFactory;
    
    /**
     * @var Random
     */
    private $random;
    
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    
    /**
     * @var JsonFactory
     */
    private $paymentTokenFactory;

    /**
     * @var VaultwebFactory
     */
    private $vaultwebFactory;
    
     /**
      * @var \Magento\Framework\Serialize\Serializer\Json
      */
    private $serializer;

    /**
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * @var PayfortClient
     */
    private $payfortClient;

    /**
     * @var ValidatorNewCardTrans
     */
    private $validatorNewCardTrans;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var TokenVarifyRequest
     */
    private $tokenVarifyRequest;


    /**
     * UpdateTokenResponse constructor.
     * @param Context $context
     * @param Config $config
     * @param EncryptorInterface $encryptor
     * @param FormFactory $formFactory
     * @param Random $random
     * @param StoreManagerInterface $storeManager
     * @param JsonFactory $resultJsonFactory
     * @param PaymentTokenFactory $paymentTokenFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param VaultwebFactory $vaultwebFactory
     * @param TokenVarifyRequest $tokenVarifyRequest
     * @param TransferFactory $transferFactory
     * @param PayfortClient $payfortClient
     * @param ValidatorNewCardTrans $validatorNewCardTrans
     */
    public function __construct(
        Context $context,
        Config $config,
        EncryptorInterface $encryptor,
        FormFactory $formFactory,
        Random $random,
        StoreManagerInterface $storeManager,
        JsonFactory $resultJsonFactory,
        PaymentTokenFactory $paymentTokenFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        VaultwebFactory $vaultwebFactory,
        TokenVarifyRequest $tokenVarifyRequest,
        TransferFactory $transferFactory,
        PayfortClient $payfortClient,
        ValidatorNewCardTrans $validatorNewCardTrans
    ) {
         parent::__construct($context);
         $this->payfortConfig = $config;
         $this->urlBuilder = $context->getUrl();
         $this->encryptor = $encryptor;
         $this->formFactory = $formFactory;
         $this->random = $random;
         $this->storeManager = $storeManager;
         $this->resultJsonFactory = $resultJsonFactory;
         $this->paymentTokenFactory = $paymentTokenFactory;
         $this->customerSession = $customerSession;
         $this->serializer = $serializer;
         $this->vaultwebFactory = $vaultwebFactory;
         $this->tokenVarifyRequest = $tokenVarifyRequest;
         $this->transferFactory = $transferFactory;
         $this->payfortClient = $payfortClient;
         $this->validatorNewCardTrans = $validatorNewCardTrans;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $request = $this->getRequest();
        $response = $request->getParams();
        if ($response['response_code']==self::TOKENSUCCESS) {
            try {
                $buildresult = $this->tokenVarifyRequest->prepareCaptureRequest($response);
                $tranferresult = $this->transferFactory->create($buildresult);
                $responsresult = $this->payfortClient->placeRequest($tranferresult);
                $validateresponse = ['response' => $responsresult];
                $validationresult = $this->validatorNewCardTrans->validate($validateresponse);
                if (!$validationresult->isValid()) {
                    $this->messageManager->addError(__("Transaction Declined."));
                    return $this->_redirect('vault/cards/listaction/');
                }
                $buildrefundresult = $this->tokenVarifyRequest->prepareRefundRequest($responsresult);
                $tranferRefundresult = $this->transferFactory->create($buildrefundresult);
                $this->payfortClient->placeRequest($tranferRefundresult);
                $expiry_date = $response['expiry_date'];
                $exp_yr = substr($expiry_date, 0, 2);
                $exp_mt = substr($expiry_date, 2, 4);
                $expirationDate = $exp_mt . $exp_yr;
                $expDate = new \DateTime(
                    $exp_yr
                    . '-'
                    . $exp_mt
                    . '-'
                    . '01'
                    . ' '
                    . '00:00:00',
                    new \DateTimeZone('UTC')
                );
                $expDate->add(new \DateInterval('P1M'));
                $expDateFull =  $expDate->format('Y-m-d 00:00:00');
                $last4cc = substr($response['card_number'], -4);
                $vaultCard = [];
                $vaultCard['gateway_token'] = $response['token_name'];
                $vaultCard['customer_id'] = $this->customerSession->getCustomer()->getId();
                $vaultCard['is_active'] = true;
                $vaultCard['is_visible'] = true;
                $vaultCard['payment_method_code'] = 'md_payfort';
                $vaultCard['type'] = 'card';
                $vaultCard['expires_at'] = $expDateFull;
                $cardholder = $response['card_holder_name'];
                $nameArray = explode(" ", $cardholder);
                $firstname = $nameArray[0];
                $lastname  = $nameArray[1];
                $vaultCard['details'] = $this->convertDetailsToJSON([
                    'type' => $this->getCreditCardType($response['card_bin']),
                    'maskedCC' => $last4cc,
                    'expirationDate' => $expirationDate,
                    'firstname' => $firstname,
                    'lastname' => $lastname
                ]);
                $vaultCard['public_hash'] = $this->generatePublicHash($vaultCard);
                $merchantref = $response['merchant_reference'];
                $merchrefArray = explode('_', $merchantref);
                $cardid = $merchrefArray['0'];
                $paymentTokenModel = $this->paymentTokenFactory->create()->load($cardid);
                $paymentTokenModel->setPublicHash($vaultCard['public_hash']);
                $paymentTokenModel->setGatewayToken($vaultCard['gateway_token']);
                $paymentTokenModel->setExpiresAt($vaultCard['expires_at']);
                $paymentTokenModel->setDetails($vaultCard['details']);
                $paymentTokenModel->save();
                $vaultwebModel = $this->vaultwebFactory->create();
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
                $vaultwebModel->setWebsiteId($websiteId);
                $vaultwebModel->setVaultToken($vaultCard['gateway_token']);
                $vaultwebModel->save();
                $this->messageManager->addSuccess(__("Card has been Updated Successfully."));
                return $this->_redirect('vault/cards/listaction/');
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __($e->getMessage()));
                return $this->_redirect('vault/cards/listaction/');
            }
        } else {
            $this->messageManager->addError($response['response_message']);
            return $this->_redirect('vault/cards/listaction/');
        }
    }
    
    private function convertDetailsToJSON($details)
    {
        $json = $this->serializer->serialize($details);
        return $json ? $json : '{}';
    }
    
    function getCreditCardType($str, $format = 'string')
    {
        if (empty($str)) {
            return false;
        }

        $matchingPatterns = [
            'VI' => '/^4[0-9]{0,}$/',
            'MC' => '/^(5[1-5]|222[1-9]|22[3-9]|2[3-6]|27[01]|2720)[0-9]{0,}$/',
            'AE' => '/^3[47][0-9]{0,}$/',
        ];

        $ctr = 1;
        foreach ($matchingPatterns as $key => $pattern) {
            if (preg_match($pattern, $str)) {
                return $format == 'string' ? $key : $ctr;
            }
            $ctr++;
        }
    }
    
    protected function generatePublicHash($vaultCard)
    {
        $hashKey = $vaultCard['gateway_token'];
        if ($vaultCard['customer_id']) {
            $hashKey .= $vaultCard['customer_id'];
        }

        $hashKey .= $vaultCard['payment_method_code']
                . $vaultCard['type']
                . $vaultCard['details'];

        return $this->encryptor->getHash($hashKey);
    }
}

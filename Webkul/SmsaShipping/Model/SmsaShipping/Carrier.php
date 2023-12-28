<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_SmsaShipping
 * @author     Webkul
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\SmsaShipping\Model\SmsaShipping;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\Module\Dir;
use Magento\Framework\Xml\Security;
use Magento\Framework\HTTP\AsyncClient\Request;

/**
 * Free shipping model
 *
 * @api
 * @since 100.0.2
 */
class Carrier extends \Magento\Shipping\Model\Carrier\AbstractCarrierOnline implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'smsa';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var $_defaultGatewayUrl
     */
    protected $_defaultGatewayUrl = 'http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?WSDL';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var string
     */
    protected $_shipServiceWsdl;

    /**
     * construct
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Xml\Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\SmsaShipping\Logger\Logger $SmsaLogger
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\SmsaShipping\Logger\Logger $SmsaLogger,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->countryFactory = $countryFactory->create();
        $this->regionFactory = $regionFactory;
        $this->_httpClientFactory = $httpClientFactory;
        $this->customerSession = $customerSession;
        $this->trackFactory = $trackFactory;
        $this->_trackErrorFactory = $trackErrorFactory;
        $this->_trackStatusFactory = $trackStatusFactory;
        $this->SmsaLogger = $SmsaLogger;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * Create soap client with
     * @return \SoapClient
     */
    protected function _createSoapClient()
    {
        // ini_set("soap.wsdl_cache_enabled", 0);
        $url    = $this->_defaultGatewayUrl;
        $client = new \SoapClient($url, ["soap_version" => SOAP_1_1,"trace" => 1, "cache_wsdl" => WSDL_CACHE_NONE]);
        return $client;
    }

    /**
     * Rates Collector
     *
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $this->setRequest($request);
        $shippingpricedetail = $this->getShippingPricedetail($request);

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        if (isset($shippingpricedetail['error']) && $shippingpricedetail['error'] == true) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('smsa');
            $error->setCarrierTitle($this->getConfigData('title'));
            if ($this->getConfigData('specificerrmsg')) {
                $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            } else {
                return false;
            }
            $result->append($error);
        } else {
            $method = $this->_rateMethodFactory->create();
            $method->setCarrier('smsa');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('smsa');
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice($shippingpricedetail['handlingfee']);
            $method->setCost($shippingpricedetail['handlingfee']);
            $result->append($method);
        }
        return $result;
    }

    /**
     * get shipping price
     *
     * @param RateRequest $request
     * @return void
     */
    public function getShippingPricedetail($request)
    {
        try {
            $r = $request;
            $error = false;
            $weight = 0;
            $shippingAmount = [];
            foreach ($r->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                $origincountrycode = $this->_scopeConfig->getValue('shipping/origin/country_id');
                if ($origincountrycode == "" || ($origincountrycode != 'AE' && $origincountrycode != 'SA')) {
                    $error = true;
                }

                if (isset($item['weight']) && ($item['weight'] == 0.0 || $item['weight'] > 35)) {
                    $error = true;
                }
                
                $weight = $weight + $item['weight'] * $item['qty'];
                
            }
            if ($weight <= 15) {
                $rate = $this->getConfigData('price_for_fifteen_kg');
            } else {
                $extraWeight = $weight - 15;
                $extraWeightRate = $extraWeight * $this->getConfigData('price_for_additional_kg');
                $rate = $this->getConfigData('price_for_fifteen_kg') + $extraWeightRate;
            }
            $result = ['handlingfee' => $rate,  'error' => $error];
            return $result;
        } catch (\Exception $e) {
            $this->SmsaLogger->info($e->getMessage());
            $result = ['handlingfee' => 0,  'error' => true];
            return $result;
        }
    }

    /**
     * create shipment label
     *
     * @param \Magento\Framework\DataObject $request
     * @return void
     */
    public function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->_prepareShipmentRequest($request);
        $requestClient = $this->_createShipmentRequest($request);

        $result = new \Magento\Framework\DataObject();

        if (is_object($requestClient)) {
            if (str_contains($requestClient->addShipmentResult, 'Failed')) {
                $debugData['result'] = [
                    'error' => $requestClient->addShipmentResult,
                ];
                $this->_debug($debugData);
                $result->setErrors($debugData['result']['error']);
            } else {
                $shipLabel = $this->getShippingLabelContent($requestClient);
                $result->setShippingLabelContent($shipLabel->getPDFResult);
                $result->setTrackingNumber($requestClient->addShipmentResult);

                $shipmentData = [
                    'api_name' => 'SMSA',
                    'tracking_number' => $requestClient->addShipmentResult
                ];
                $this->customerSession->setData('shipment_data', $shipmentData);
            }
        }
        return $result;
    }

    /**
     * get shipping label pdf content
     *
     * @param [type] $requestClient
     * @return void
     */
    public function getShippingLabelContent($requestClient)
    {
        $passKey = $this->getConfigData('pass_key');
        $service_param =  [
            'getPDF' => [
                'awbNo' => $requestClient->addShipmentResult,
                'passkey' => $passKey
            ]
        ];
        $client = $this->_createSoapClient();
        $responseBody = $client->__soapCall("getPDF", $service_param);
        return $responseBody;
    }

    /**
     * create shipment request
     *
     * @param \Magento\Framework\DataObject $request
     * @return void
     */
    public function _createShipmentRequest($request)
    {
        $passKey = $this->getConfigData('pass_key');
        $service_param =  [
            'addShipment' => [
                'PCs'       => count($request->getPackages()),
                'passKey'   => $passKey,
                'refNo'     => rand(),
                'sentDate'  => date('Y/m/d'),
                'idNo'      => '',
                'cName'     => $request->getRecipientContactPersonFirstName(),
                'cntry'     => $request->getRecipientAddressCountryCode(),
                'cCity'     => $request->getRecipientAddressCity(),
                'cZip'      => '',
                'cPOBox'    => '',
                'cMobile'   => $request->getRecipientContactPhoneNumber(),
                'cTel1'     => '',
                'cTel2'     => '',
                'cAddr1'    => $request->getRecipientAddressStreet1(),
                'cAddr2'    => $request->getRecipientAddressStreet2(),
                'shipType'  => 'DLV',
                'cEmail'    => $request->getRecipientEmail(),
                'carrValue' => '',
                'carrCurr'  => '',
                'codAmt'    => '',
                'weight'    => $request->getPackageWeight(),
                'custVal'   => '',
                'custCurr'  => '',
                'insrAmt'   => '',
                'insrCurr'  => '',
                'itemDesc'  => '',
            ]
        ];

        try {
            $client = $this->_createSoapClient();
            $responseBody = $client->__soapCall("addShipment", $service_param);
            $debugData['result'] = $responseBody;
            if ($responseBody == "") {
                throw new \Magento\Framework\Exception\LocalizedException("No XML returned [" . __LINE__ . "]");
            }
        } catch (\Exception $e) {
            $this->SmsaLogger->info($e->getMessage());
            $this->_errors[$e->getCode()] = $e->getMessage();
            $responseBody = '';
        }

        $this->_debug($debugData);

        return $responseBody;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result|null
     */
    public function getTracking($trackings)
    {
        $this->_result = $this->trackFactory->create();
        foreach ((array) $trackings as $code) {
            $this->_getTrackingFromWS($code);
        }
        return $this->_result;
    }

    /**
     * Send request for tracking
     *
     * @param string[] $trackings
     * @return void
     */
    public function getTrackingInfo($tracking)
    {
        $result = $this->getTracking($tracking);
        if ($result instanceof \Magento\Shipping\Model\Tracking\Result) {
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }
        return false;
    }

    /**
     * get tracking info
     *
     * @param string $tracking
     * @return void
     */
    public function _getTrackingFromWS($tracking)
    {
        $passKey = $this->getConfigData('pass_key');
        $service_param =  [
            'getTracking' => [
                'awbNo' => $tracking,
                'passkey' => $passKey
            ]
        ];
        $this->_debug(json_encode($service_param));
        try {
            $client = $this->_createSoapClient();
            $response = $client->__soapCall("getTracking", $service_param);

            $this->_debug(json_encode($response));

            if (count((array)$response)) {
                if (isset($response->getTrackingResult->any)) {

                    $xml = new \SimpleXMLElement($response->getTrackingResult->any);

                    foreach ($xml->NewDataSet->Tracking as $tracking) {
                        $date = date_create($tracking->Date);
                        $formatTime = date_format($date, "H:i:s");
                        $formatDate =  date_format($date, "Y-m-d");
                        $trackingProgress = [
                            'deliverydate'     => $formatDate,
                            'deliverytime'     => $formatTime,
                            'deliverylocation' => $tracking->Location,
                            'activity'         => $tracking->Activity
                        ];
                        $progress[] = $trackingProgress;
                    }
                    $trackData                   = $progress[0];
                    $trackData['progressdetail'] = $progress;
                    $carrierTitle = $this->getConfigData('title');

                    $track = $this->_trackStatusFactory->create();

                    $track->setTracking($tracking->awbNo)
                        ->setCarrierTitle($carrierTitle)
                        ->setCarrier('smsa')
                        ->addData($trackData);
                    $this->_result->append($track);
                    return true;

                }
            } else {
                $error = $this->_trackErrorFactory->create();
                $error->setTracking($tracking);
                $error->setCarrierTitle($this->getConfigData('title'));
                $this->_result->append($error);
                return false;
            }
        } catch (\Exception $e) {
            $this->SmsaLogger->info($e->getMessage());
            $this->_errors[$e->getCode()] = $e->getMessage();
            return false;
        }
    }

    /**
     * Processing additional validation to check if carrier applicable.
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|\Magento\Framework\DataObject
     */
    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        return true;
    }

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|\Magento\Framework\DataObject
     * @since 100.2.6
     */
    public function processAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        return true;
    }

    /**
     * Returns allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['smsa' => $this->getConfigData('name')];
    }
}

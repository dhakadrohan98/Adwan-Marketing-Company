<?php
/**
 * @category  Sigma
 * @package   Sigma_CheckValidZipCodes
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\CheckValidZipCodes\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Catalog\Block\Product\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Resolver for Check Valid ZipCodes
 */
class CheckZipCodes implements ResolverInterface
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Store Interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * zip codes config path
     */
    private const XML_PATH_ZIP_CODES = 'global_config/zip_code_checker/zipcodes';

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param data $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        /**
        * Get zipcode from variable
        */
        $zipCode = $this->getString($args);

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $configZipCodeValue = $this->scopeConfig->getValue(self::XML_PATH_ZIP_CODES, $storeScope);

        $validZipCode = explode(',', $configZipCodeValue);
        if (in_array($zipCode, $validZipCode)) {
            $message['message'] = "Zip Code is available";
            $message['status'] = "1";
        } else {
            $message['message'] = "Zip Code is not available";
            $message['status'] = "0";
        }

        return $message;
    }

    /**
     * Passed Zip Code from graphql query
     *
     * @param args $args
     *
     * @throws GraphQlNoSuchEntityException
     */
    private function getString(array $args)
    {
        if (!isset($args['zipcode'])) {
            throw new GraphQlInputException(__('"Zip Code value should be specified'));
        }
        return $args['zipcode'];
    }
}

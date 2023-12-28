<?php
/**
 * @category  Sigma
 * @package   Sigma_GcaptchaVerifyGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\GcaptchaVerifyGraphQl\Model\Resolver;

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
 * Resolver for Verify Captcha
 */
class VerifyCaptcha implements ResolverInterface
{

    /**
     * ScopeConfig Interface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * ScopeConfig Interface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_storeManager;

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
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
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

        // @codingStandardsIgnoreStart
        $secretKey = $this->_scopeConfig->getValue('recaptcha_frontend/type_recaptcha/private_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE); // private_key
        // @codingStandardsIgnoreEnd

        $recaptchaResponse = $this->getString($args);

        $response = null;
        
        $path = 'https://www.google.com/recaptcha/api/siteverify?';
        
        // @codingStandardsIgnoreStart
        $response = file_get_contents($path."secret=$secretKey&response=$recaptchaResponse");
        // @codingStandardsIgnoreEnd
        
        $responseData = json_decode($response);

        if ($responseData->success) {
            $message['message'] = "g-recaptcha Verified successfully";
        } else {
            $message['message'] = "Some error in Verifying g-recaptcha";
        }

            return $message;
    }
        
    /**
     * Passed String from graphql query
     *
     * @param array $args
     */
    private function getString(array $args)
    {
        if (!isset($args['recaptcha_response'])) {
            throw new GraphQlInputException(__('"value should be specified'));
        }
        return $args['recaptcha_response'];
    }
}

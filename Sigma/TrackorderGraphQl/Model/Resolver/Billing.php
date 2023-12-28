<?php
/**
 * @category  Sigma
 * @package   Sigma_TrackorderGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\TrackorderGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
 
/**
 * Retrieves the Billing information object
 */
class Billing implements ResolverInterface
{

    /**
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->_countryFactory = $countryFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['billing_address'])) {
             return null;
        }
        $billingData = $value['billing_address'];
       
        $billingAddress = [];
        $billingAddress['billing']['name'] = $billingData['firstname'].' '.$billingData['lastname'];
        $billingAddress['billing']['street'] = $billingData['street'];
        $billingAddress['billing']['city'] = $billingData['city'];
        $billingAddress['billing']['region'] = $billingData['region'];
        $billingAddress['billing']['country'] = $this->getCountryname($billingData['country_id']);
        $billingAddress['billing']['postcode'] = $billingData['postcode'];
        $billingAddress['billing']['telephone'] = $billingData['telephone'];
        $billingAddress['billing']['company'] = $billingData['company'];
        return $billingAddress;
    }

    /**
     * Get Country name by country code
     *
     * @param string $countryCode
     */
    public function getCountryname($countryCode)
    {
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }
}

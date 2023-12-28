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
 * Retrieves the Shipping information object
 */
class Shipping implements ResolverInterface
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
        if (!isset($value['shipping_address'])) {
             return null;
        }
        $shippingData = $value['shipping_address'];
        $shippingAddress = [];
        $shippingAddress['shipping']['name'] = $shippingData['firstname'].' '.$shippingData['lastname'];
        $shippingAddress['shipping']['street'] = $shippingData['street'];
        $shippingAddress['shipping']['city'] = $shippingData['city'];
        $shippingAddress['shipping']['region'] = $shippingData['region'];
        $shippingAddress['shipping']['country'] = $this->getCountryname($shippingData['country_id']);
        ;
        $shippingAddress['shipping']['postcode'] = $shippingData['postcode'];
        $shippingAddress['shipping']['telephone'] = $shippingData['telephone'];
        $shippingAddress['shipping']['company'] = $shippingData['company'];
        return $shippingAddress;
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

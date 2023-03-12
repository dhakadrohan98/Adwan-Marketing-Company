<?php
/**
 * @category  Sigma
 * @package   Sigma_ProductsAdditionalDataGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

namespace Sigma\ProductsAdditionalDataGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver for Products Additional csutom attribute data
 */
class Productsgraphql implements ResolverInterface
{
    /**
     * @var \Sigma\ProductsAdditionalDataGraphQl\Model\Resolver\DataProvider\Productsgraphql
     */
    private $productsgraphqlDataProvider;

    /**
     * @param DataProvider\Productsgraphql $productsgraphqlDataProvider
     */
    public function __construct(
        \Sigma\ProductsAdditionalDataGraphQl\Model\Resolver\DataProvider\Productsgraphql $productsgraphqlDataProvider
    ) {
        $this->productsgraphqlDataProvider = $productsgraphqlDataProvider;
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
        $sku = $this->getSku($args);
        $manufacturerId = $this->getManufacturerId($args);
        $productsData = $this->productsgraphqlDataProvider->getAttributesBySku($sku, $manufacturerId);

        return $productsData;
    }

    /**
     * Passed sku from graphql query
     *
     * @param args $args
     *
     * @throws GraphQlNoSuchEntityException
     */
    private function getSku(array $args)
    {
        if (!isset($args['sku'])) {
            throw new GraphQlInputException(__('"SKU should be specified'));
        }
        return $args['sku'];
    }

    /**
     * Passed manufacturer Id from graphql query
     *
     * @param args $args
     *
     * @throws GraphQlNoSuchEntityException
     */
    private function getManufacturerId(array $args)
    {
        if (!isset($args['manufacturerId'])) {
            throw new GraphQlInputException(__('"manufacturer Id should be specified'));
        }
        return $args['manufacturerId'];
    }
}

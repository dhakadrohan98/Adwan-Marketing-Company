<?php
/**
 * @category  Sigma
 * @package   Sigma_ProductSpecificationsGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

namespace Sigma\ProductSpecificationsGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver for Products Additional custom attribute data
 */
class ProductSpecifications implements ResolverInterface
{
    /**
     * @var \Sigma\ProductSpecificationsGraphQl\Model\Resolver\DataProvider\Productsgraphql
     */
    private $productsgraphqlDataProvider;

    /**
     * @param DataProvider\Productsgraphql $productsgraphqlDataProvider
     */
    public function __construct(
        \Sigma\ProductSpecificationsGraphQl\Model\Resolver\DataProvider\Productsgraphql $productsgraphqlDataProvider
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
        $productsData = $this->productsgraphqlDataProvider->getAttributesBySku($sku);

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
}

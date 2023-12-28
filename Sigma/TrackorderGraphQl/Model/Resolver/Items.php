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
use Magento\CatalogGraphQl\Model\Resolver\Product\Websites\Collection;
use Magento\Framework\Pricing\Helper\Data as PriceFormating;
 
/**
 * Retrieves the Items information object
 */
class Items implements ResolverInterface
{
    /**
     * @var PriceFormating
     */
    protected $priceHelper;

    /**
     * @param PriceFormating $priceHelper
     */
    public function __construct(
        PriceFormating $priceHelper
    ) {
        $this->priceHelper  = $priceHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['items'])) {
             return null;
        }
        $itemArray = [];
        foreach ($value['items'] as $key => $item) {
            $itemArray[$key]['sku'] = $item['sku'];
            $itemArray[$key]['title'] = $item['name'];
            $itemArray[$key]['price'] = $item['price'];
            //$itemArray[$key]['price'] = $this->priceHelper->currency($item['price'], true, false);
        }
        return $itemArray;
    }
}

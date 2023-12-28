<?php
/**
 * @category  Sigma
 * @package   Sigma_RecentlyViewedProducts
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\RecentlyViewedProducts\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver for Add Recently Viewed Products
 */
class AddRecentlyViewedProducts implements ResolverInterface
{
    /**
     * @var \Sigma\RecentlyViewedProducts\Model\Resolver\DataProvider\AddRecentlyViewed
     */
    private $recentlyViewedDataProvider;

    /**
     * @param recentlyViewedDataProvider $recentlyViewedDataProvider
     */
    public function __construct(
        \Sigma\RecentlyViewedProducts\Model\Resolver\DataProvider\AddRecentlyViewed $recentlyViewedDataProvider
    ) {
        $this->recentlyViewedDataProvider = $recentlyViewedDataProvider;
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
        $customerId = $args['input']['customer_id'];
        $productId = $args['input']['product_id'];

        $success_message = $this->recentlyViewedDataProvider->addData(
            $customerId,
            $productId
        );
        return $success_message;
    }
}

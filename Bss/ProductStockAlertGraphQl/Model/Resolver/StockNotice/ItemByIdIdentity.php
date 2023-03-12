<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlertGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ProductStockAlertGraphQl\Model\Resolver\StockNotice;

class ItemByIdIdentity implements \Magento\Framework\GraphQl\Query\Resolver\IdentityInterface
{
    /**
     * const
     */
    const CACHE_TAG = 'bss_stock_notice_item_tag_';

    /**
     * @var string
     */
    private $cacheTag = self::CACHE_TAG;

    /**
     * @param array $resolvedData
     * @return array
     */
    public function getIdentities(
        array $resolvedData
    ): array {
        $ids = [];
        if (isset($resolvedData['alert_stock_id']) &&
            isset($resolvedData['customer_id']) &&
            isset($resolvedData['product_sku']) &&
            isset($resolvedData['website_id'])) {
            $ids[] = sprintf(
                '%s_%s',
                $this->cacheTag,
                $resolvedData['alert_stock_id'] .
                $resolvedData['customer_id'] .
                $resolvedData['product_sku'] .
                $resolvedData['website_id']
            );
        }
        if (!empty($ids)) {
            $ids[] = $this->cacheTag;
        }
        return $ids;
    }
}

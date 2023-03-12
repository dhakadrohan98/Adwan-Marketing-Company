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

class ListByCustomerIdentity implements \Magento\Framework\GraphQl\Query\Resolver\IdentityInterface
{
    /**
     * const
     */
    const CACHE_TAG = 'bss_stock_notice_list_item_tag_';

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
        if (!empty($resolvedData)) {
            foreach ($resolvedData as $item) {
                if (isset($item['alert_stock_id']) &&
                    isset($item['customer_id']) &&
                    isset($item['product_sku']) &&
                    isset($item['website_id'])) {
                    $ids[] = sprintf(
                        '%s_%s',
                        $this->cacheTag,
                        $item['alert_stock_id'] .
                        $item['customer_id'] .
                        $item['product_sku'] .
                        $item['website_id']
                    );
                }
            }
        }
        if (!empty($ids)) {
            $ids[] = $this->cacheTag;
        }
        return $ids;
    }
}

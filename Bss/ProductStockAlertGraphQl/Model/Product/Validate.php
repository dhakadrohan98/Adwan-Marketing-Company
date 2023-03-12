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

namespace Bss\ProductStockAlertGraphQl\Model\Product;

class Validate
{
    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param null|\Magento\Catalog\Api\Data\ProductInterface $parent
     * @return bool
     */
    public function validateChildProduct($product, $parent): bool
    {
        if (in_array($parent->getTypeId(), ['simple', 'virtual', 'downloadable'])) {
            return $parent->getId() === $product->getId();
        } elseif ($parent->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $childs = $parent->getTypeInstance()->getUsedProducts($parent);
            foreach ($childs as $child) {
                if ($child->getId() === $product->getId()) {
                    return true;
                }
            }
            return false;
        } elseif ($parent->getTypeId() === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            $childs = $parent->getTypeInstance()->getAssociatedProducts($parent);
            foreach ($childs as $child) {
                if ($child->getId() === $product->getId()) {
                    return true;
                }
            }
            return false;
        } elseif ($parent->getTypeId() === \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            $childIdsArr = $parent->getTypeInstance()->getChildrenIds($parent->getId(), false);
            foreach ($childIdsArr as $childIds) {
                if (in_array($product->getId(), $childIds)) {
                    return true;
                }
                continue;
            }
            return false;
        }
        return false;
    }
}

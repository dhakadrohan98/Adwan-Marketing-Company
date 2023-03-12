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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection
     */
    protected $bundle;

    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $grouped;

    /**
     * @var \Bss\ProductStockAlert\Model\Stock
     */
    protected $modelstock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Bss\ProductStockAlert\Model\ResourceModel\Stock
     */
    protected $stock;

    /**
     * UpgradeData constructor.
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable
     * @param \Magento\Bundle\Model\ResourceModel\Selection $bundle
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped
     * @param \Bss\ProductStockAlert\Model\Stock $modelstock
     * @param \Bss\ProductStockAlert\Model\ResourceModel\Stock $stock
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        \Magento\Bundle\Model\ResourceModel\Selection $bundle,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped,
        \Bss\ProductStockAlert\Model\Stock $modelstock,
        \Bss\ProductStockAlert\Model\ResourceModel\Stock $stock,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
    ) {
        $this->stock = $stock;
        $this->configurable = $configurable;
        $this->bundle = $bundle;
        $this->grouped = $grouped;
        $this->modelstock = $modelstock;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.6', '<=')) {
            $eavSetup = $this->eavSetupFactory->create();
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'product_stock_alert',
                [
                    'type' => 'int',
                    'label' => 'Out of Stock Notification',
                    'input' => 'select',
                    'source' => \Bss\ProductStockAlert\Model\Attribute\Source\Order::class,
                    'required' => false,
                    'sort_order' => 57,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'visible' => true,
                    'is_html_allowed_on_front' => true,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true
                ]
            );

            $collection = $this->modelstock->getCollection();
            if ($collection->getSize() > 0) {
                $productIds = [];
                foreach ($collection as $alert) {
                    $productIds[] = $alert->getProductId();
                }
                $productCollection = $this->productCollectionFactory->create();
                $productCollection->addAttributeToSelect('*')->addFieldToFilter('entity_id', ['in' => $productIds]);
                $data = [];
                $parentId = null;
                foreach ($productCollection as $product) {
                    $parent = $this->getParentIdConfigurable($product);
                    if (!isset($parent[0])) {
                        $parent = $this->getParentIdBundle($product);
                    }
                    if (!isset($parent[0])) {
                        $parent = $this->getParentIdGrouped($product);
                    }
                    if (isset($parent[0])) {
                        $parentId = $parent[0];
                    }
                    $data[] = ['product_id' => $product->getId(), 'parent_id' => $parentId];
                }
                foreach ($data as $row) {
                    $this->executeQueryinRow($row, $setup);
                }

                $setup->endSetup();
            }
        }
    }

    /**
     * @param array $row
     * @param ModuleDataSetupInterface $setup
     */
    private function executeQueryinRow($row, $setup)
    {
        if ($row['parent_id']) {
            $query = "UPDATE ". $setup->getTable('bss_product_alert_stock') .
                " SET parent_id=". $row['parent_id'] . " WHERE product_id=". $row['product_id'];
            $this->stock->executeQuery($setup, $query);
        }
    }

    /**
     * @param object $product
     * @return string[]
     */
    protected function getParentIdConfigurable($product)
    {
        return $this->configurable->getParentIdsByChild($product->getId());
    }

    /**
     * @param object $product
     * @return array
     */
    protected function getParentIdBundle($product)
    {
        return $this->bundle->getParentIdsByChild($product->getId());
    }

    /**
     * @param object $product
     * @return array
     */
    protected function getParentIdGrouped($product)
    {
        return $this->grouped->getParentIdsByChild($product->getId());
    }
}

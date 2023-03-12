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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.6', '<=')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('bss_product_alert_stock'),
                'parent_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => true,
                    'comment' => 'Parent ID'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.1', '<=')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('bss_product_alert_stock'),
                'store_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => null,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' =>'store_id'
                ]
            );
        }
        $installer->endSetup();
    }
}

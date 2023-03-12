<?php
/**
 * Copyright © 2015 Dotsquares. All rights reserved.
 */

namespace Dotsquares\FilesCollection\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('dotsquares_filescollection_items'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'File Name'
            )
			 ->addColumn(
                'file_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Upload File'
            )
			->addColumn(
                'product_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Product List'
            )
			->addColumn(
                'categories',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Category List'
            )
			->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['default' => null],
                'Status'
            );
			
			
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

/**
 * Class UpgradeSchema
 *
 * @package Bss\CompanyAccount\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws Zend_Db_Exception
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addColumnSubUserId($installer);
        }

        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            $this->addColumnSubUserIdToOauthToken($installer);
        }
        $installer->endSetup();
    }

    /**
     * Add column sub_user_id in table quote_extension
     *
     * @param SchemaSetupInterface $installer
     * @throws LocalizedException
     */
    public function addColumnSubUserId($installer)
    {
        $tableName = $installer->getTable('quote_extension');
        if ($installer->tableExists($tableName)) {
            if (!$installer->getConnection()->tableColumnExists($installer->getTable($tableName), "sub_user_id")) {
                $installer->getConnection()->addColumn(
                    $installer->getTable('quote_extension'),
                    'sub_user_id',
                    [
                        'type' => Table::TYPE_SMALLINT,
                        'nullable' => true,
                        'comment' => 'Sub User Id'
                    ]
                );
            }
        }
    }

    /**
     * Add column sub_user_id to oauth token table for sub-user token generation
     *
     * @param SchemaSetupInterface $setup
     * @since 1.0.6
     */
    protected function addColumnSubUserIdToOauthToken(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('oauth_token');

        if ($setup->tableExists($table) &&
            !$setup->getConnection()->tableColumnExists(
                $table,
                "sub_user_id"
            )
        ) {
            $setup->getConnection()->addColumn(
                $table,
                "sub_user_id",
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'unsigned' => true,
                    'comment' => 'Sub User Id',
                    'after' => 'customer_id'
                ]
            );
            $setup->getConnection()->addIndex(
                $table,
                $setup->getIdxName('oauth_token', ['sub_user_id']),
                ['sub_user_id']
            );
            $setup->getConnection()->addForeignKey(
                $setup->getFkName(
                    'oauth_token',
                    'sub_user_id',
                    $setup->getTable("bss_sub_user"),
                    'sub_id'
                ),
                $table,
                'sub_user_id',
                $setup->getTable("bss_sub_user"),
                "sub_id"
            );
        }
    }
}

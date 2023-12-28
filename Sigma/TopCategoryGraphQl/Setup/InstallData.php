<?php
/**
 * @category  Sigma
 * @package   Sigma_TopCategoryGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

namespace Sigma\TopCategoryGraphQl\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class InstallData
 * Install data Setup
 */

class InstallData implements InstallDataInterface
{
    /**
     * Eav factory setup
     *
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
    /**
     * Add category attribute top_category
     *
     * Add category attribute thumbnail_image
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface|ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface|ModuleContextInterface $context
     * @return void
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        /* Create  attribute thumbnail_image for category*/
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'thumbnail_image',
            [
                'type'         => 'varchar',
                'label'        => 'Thumbnail Image',
                'input'        => 'image',
                'backend'      => Magento\Catalog\Model\Category\Attribute\Backend::IMAGE,
                'sort_order'   => 110,
                'source'       => '',
                'global'       => 1,
                'visible'      => true,
                'required'     => false,
                'user_defined' => false,
                'default'      => null,
                'group'        => '',

            ]
        );
        /* Create attribute top_category for category */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'top_category',
            [
                'type'         => 'varchar',
                'label'        => 'Top Category',
                'input'        => 'boolean',
                'sort_order'   => 100,
                'source'       => '',
                'global'       => 1,
                'visible'      => true,
                'required'     => false,
                'user_defined' => false,
                'default'      => null,
                'group'        => '',
                'backend'      => ''
            ]
        );
    }
}

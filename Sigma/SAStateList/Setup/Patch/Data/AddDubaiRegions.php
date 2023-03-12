<?php
/**
 * @category  Sigma
 * @package   Sigma_SAStateList
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types = 1);

namespace Sigma\SAStateList\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddDubaiRegions implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {

        /**
         * Fill table directory/country_region
         * Fill table directory/country_region_name for ar_SA locale
         * Fill table directory/country_region_name for en_US locale
         */
        $data = [
            ['SA','Al-Bahah','Al-Bahah'],
            ['SA','Al-Jaw f','Al-Jaw f'],
            ['SA','Al-Qassim','Al-Qassim'],
            ['SA','Asir','Asir'],
            ['SA','Eastern','Eastern'],
            ['SA','Hail','Madeina'],
            ['SA','Jazan','Jazan'],
            ['SA','Madeina','Madeina'],
            ['SA','Makkah','Makkah'],
            ['SA','Najran','Najran'],
            ['SA','Northern Borders','Northern Borders'],
            ['SA','Riyadh','Riyadh'],
            ['SA','Tabuk','Tabuk']
        ];
// @codingStandardsIgnoreFile
        foreach ($data as $row) {
            $bind = ['country_id' => $row[0], 'code' => $row[1], 'default_name' => $row[2]];
            $this->moduleDataSetup->getConnection()->insert(
                $this->moduleDataSetup->getTable('directory_country_region'),
                $bind
            );
            $regionId = $this->moduleDataSetup->getConnection()->lastInsertId(
                $this->moduleDataSetup->getTable('directory_country_region')
            );

            $bind = ['locale' => 'en_US', 'region_id' => $regionId, 'name' => $row[2]];
            $this->moduleDataSetup->getConnection()->insert(
                $this->moduleDataSetup->getTable('directory_country_region_name'),
                $bind
            );

            $bind = ['locale' => 'ar_SA', 'region_id' => $regionId, 'name' => $row[2]];
            $this->moduleDataSetup->getConnection()->insert(
                $this->moduleDataSetup->getTable('directory_country_region_name'),
                $bind
            );
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}

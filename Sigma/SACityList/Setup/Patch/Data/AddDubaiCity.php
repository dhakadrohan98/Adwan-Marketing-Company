<?php
/**
 * @category  Sigma
 * @package   sSigma_SACityList
 * @author    SigmaInfo Team
 * @copyright 2022 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types = 1);

namespace Sigma\SACityList\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddDubaiCity implements DataPatchInterface
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
         * Fill table directory/region_city
         * Fill table directory/region_city_name for ar_SA locale
         * Fill table directory/region_city_name for en_US locale
         */
        $data = [
            [1249,'Aqiq'],
            [1249,'Baha'],
            [1249,'Biljurashi'],
            [1249,'Mandaq'],
            [1249,'Mukhwah'],
            [1249,'Qilwah'],
            [1250,'Dawmat Al Jandal'],
            [1250,'Qurayyat'],
            [1250,'Skakah'],
            [1250,'Tabarjal'],
            [1251,'Ash Shimasiyah'],
            [1251,'Asiyah'],
            [1251,'Badaya'],
            [1251,'Bikeryah'],
            [1251,'Buraydah'],
            [1251,'Midhnab'],
            [1251,'Rass'],
            [1251,'Riyadh Al Khabra'],
            [1251,'Unayzah'],
            [1251,'Uqlat As Suqur'],
            [1251,'Uyun Al Jiwa'],
            [1252,'Abha'],
            [1252,'Ahd Rafidah'],
            [1252,'Bareq'],
            [1252,'Bashayer'],
            [1252,'Billasmar'],
            [1252,'Bishah'],
            [1252,'Dhahran Al Janoub'],
            [1252,'Khamis Mushayt'],
            [1252,'Majarda'],
            [1252,'Muhayil'],
            [1252,'Namas'],
            [1252,'Rijal Alma'],
            [1252,'Sabt Al Alaya'],
            [1252,'Sarat Abida'],
            [1252,'Tanumah'],
            [1252,'Tareeb'],
            [1252,'Tathleeth'],
            [1252,'Wadeen'],
            [1252,'Wadi Bin Hashbal'],
            [1253,'Anak'],
            [1253,'Buqaiq'],
            [1253,'Dammam'],
            [1253,'Dhahran'],
            [1253,'Hafer Al Baten'],
            [1253,'Hufuf'],
            [1253,'Jubail'],
            [1253,'Khafji'],
            [1253,'Khobar'],
            [1253,'Nairiyah'],
            [1253,'Qarya Ulya'],
            [1253,'Qatif'],
            [1253,'Qaysumah'],
            [1253,'Ras Tannurah'],
            [1253,'Safwa'],
            [1253,'Sayhat'],
            [1253,'Tarout'],
            [1254,'Al Hait'],
            [1254,'Ash Shamli'],
            [1254,'Ash Shinan'],
            [1254,'Baqaa'],
            [1254,'Hail'],
            [1255,'Abu Arish'],
            [1255,'Ahad Al Masarhah'],
            [1255,'Alaradah'],
            [1255,'Baysh'],
            [1255,'Darb'],
            [1255,'Dayer'],
            [1255,'Dhamad'],
            [1255,'Edabi'],
            [1255,'Farasn Island'],
            [1255,'Jizan'],
            [1255,'Sabya'],
            [1255,'Samtah'],
            [1256,'Badr'],
            [1256,'Hanakiyah'],
            [1256,'Khayber'],
            [1256,'Madinah'],
            [1256,'Mahd Al Dahab'],
            [1256,'Sarrar'],
            [1256,'Ula'],
            [1256,'Yanbu'],
            [1256,'Yanbu Al Nakhal'],
            [1257,'Adham'],
            [1257,'Asfan'],
            [1257,'Bahrah'],
            [1257,'Dhaim'],
            [1257,'Haweyah'],
            [1257,'Jeddah'],
            [1257,'Khulais'],
            [1257,'Khurmah'],
            [1257,'Lith'],
            [1257,'Makkah'],
            [1257,'Medrekah'],
            [1257,'Mudhaylif'],
            [1257,'Muwayh'],
            [1257,'Namera'],
            [1257,'Qawz'],
            [1257,'Qunfudhah'],
            [1257,'Rabigh'],
            [1257,'Ranyah'],
            [1257,'Taif'],
            [1257,'Turbah'],
            [1257,'Wadi Hali'],
            [1257,'Wasqah'],
            [1258,'Badr Al Janoub'],
            [1258,'Habona'],
            [1258,'Najran'],
            [1258,'Sharourah'],
            [1259,'Arar'],
            [1259,'Rafha'],
            [1259,'Turayf'],
            [1259,'Uwayqilah'],
            [1260,'Afif'],
            [1260,'Aflaj'],
            [1260,'Ar Rayn'],
            [1260,'Artawiah'],
            [1260,'Bijadiyah'],
            [1260,'Dariyah'],
            [1260,'Dhilam'],
            [1260,'Dhurma'],
            [1260,'Duawdmi'],
            [1260,'Ghat'],
            [1260,'Hawtat Bani Tamim'],
            [1260,'Hawtat Sudair'],
            [1260,'Huraymila'],
            [1260,'Kharj'],
            [1260,'Majmaah'],
            [1260,'Marat'],
            [1260,'Muzamiyah'],
            [1260,'Quwayiyah'],
            [1260,'Rafaya AlGimsh'],
            [1260,'Rafiah'],
            [1260,'Remah'],
            [1260,'Riyadh'],
            [1260,'Ruwaidhah'],
            [1260,'Sajir'],
            [1260,'Shaqra'],
            [1260,'Sulayyil'],
            [1260,'Thadiq'],
            [1260,'Tumair'],
            [1260,'Wadi Al Dawsir'],
            [1260,'Zulfi'],
            [1261,'Dhuba'],
            [1261,'Haql'],
            [1261,'Tabouk'],
            [1261,'Taima'],
            [1261,'Umlej'],
            [1261,'Wajh'],
        ];
// @codingStandardsIgnoreFile
        foreach ($data as $row) {
            $bind = ['region_id' => $row[0], 'default_name' => $row[1]];
            $this->moduleDataSetup->getConnection()->insert(
                $this->moduleDataSetup->getTable('directory_region_city'),
                $bind
            );
            $cityId = $this->moduleDataSetup->getConnection()->lastInsertId(
                $this->moduleDataSetup->getTable('directory_region_city')
            );

            $bind = ['locale' => 'en_US', 'city_id' => $cityId, 'name' => $row[1]];
            $this->moduleDataSetup->getConnection()->insert(
                $this->moduleDataSetup->getTable('directory_region_city_name'),
                $bind
            );

            $bind = ['locale' => 'ar_SA', 'city_id' => $cityId, 'name' => $row[1]];
            $this->moduleDataSetup->getConnection()->insert(
                $this->moduleDataSetup->getTable('directory_region_city_name'),
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

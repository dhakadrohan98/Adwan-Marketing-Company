<?php
    
    namespace WebGate\SMSAShipping\Setup;
    
    use Magento\Framework\Setup\UninstallInterface;
    use Magento\Framework\Setup\SchemaSetupInterface;
    use Magento\Framework\Setup\ModuleContextInterface;
    
    class Uninstall implements UninstallInterface
    {
        /**
         * {@inheritdoc}
         * @SuppressWarnings(PHPMD.UnusedFormalParameter)
         */
        public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context) //@codingStandardsIgnoreLine
        {
            $setup->startSetup();
    
            if ($setup->tableExists('webgate_smsashipping_smsashippinglogs')) {
                $setup->getConnection()->dropTable($setup->getTable('webgate_smsashipping_smsashippinglogs'));
            }
    
            $setup->endSetup();
        }
    }

<?php
    
    
    namespace WebGate\SMSAShipping\Setup;
    
    use Magento\Framework\DB\Ddl\Table;
    use Magento\Framework\Setup\InstallSchemaInterface;
    use Magento\Framework\Setup\ModuleContextInterface;
    use Magento\Framework\Setup\SchemaSetupInterface;
    
    class InstallSchema implements InstallSchemaInterface
    {
        
        /**
         * {@inheritdoc}
         */
        public function install(SchemaSetupInterface $setup , ModuleContextInterface $context)
        {
            $this->smsaShippingLogs($setup);
        }
        
        
        private function smsaShippingLogs(SchemaSetupInterface $setup)
        {
            $smsaShippingLogs = $setup->getConnection()
                ->newTable($setup->getTable('webgate_smsashipping_smsashippinglogs'));
            
            $smsaShippingLogs
                ->addColumn(
                    'entity_id' ,
                    Table::TYPE_INTEGER ,
                    null ,
                    [ 'identity' => true , 'nullable' => false , 'primary' => true , 'unsigned' => true , ] ,
                    'Entity ID'
                )->addColumn(
                    'order_id' ,
                    Table::TYPE_VARBINARY ,
                    255 ,
                    [ 'nullable' => false ]
                )->addColumn(
                    'customer_id' ,
                    Table::TYPE_INTEGER ,
                    null ,
                    [ 'nullable' => false ]
                )->addColumn(
                    'customer_name' ,
                    Table::TYPE_VARBINARY ,
                    255 ,
                    [ 'nullable' => false ]
                )->addColumn(
                    'awd_status' ,
                    Table::TYPE_VARBINARY ,
                    255 ,
                    [ 'nullable' => false ]
                )->addColumn(
                    'response' ,
                    Table::TYPE_TEXT ,
                    null ,
                    [ 'nullable' => false ]
                )->addColumn(
                    'created_at' ,
                    Table::TYPE_TIMESTAMP ,
                    null ,
                    [ 'nullable' => false ]
                );
            
            $setup->getConnection()->createTable($smsaShippingLogs);
        }
    }

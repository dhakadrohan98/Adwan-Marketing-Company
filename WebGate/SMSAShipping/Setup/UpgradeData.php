<?php
    
    
    namespace WebGate\SMSAShipping\Setup;
    
    use Magento\Framework\Setup\ModuleContextInterface;
    use Magento\Framework\Setup\ModuleDataSetupInterface;
    use Magento\Framework\Setup\UpgradeDataInterface;
    use Magento\Sales\Setup\SalesSetupFactory;

    class UpgradeData implements UpgradeDataInterface
    {
        
        private $salesSetupFactory;
        
        /**
         * Constructor
         *
         * @param SalesSetupFactory $salesSetupFactory
         */
        public function __construct(SalesSetupFactory $salesSetupFactory)
        {
            $this->salesSetupFactory = $salesSetupFactory;
        }
        
        /**
         * {@inheritdoc}
         */
        public function upgrade(
            ModuleDataSetupInterface $setup ,
            ModuleContextInterface $context
        )
        {
            if(version_compare($context->getVersion() , "1.0.2" , "<"))
            {
                
                $salesSetup = $this->salesSetupFactory->create([ 'setup' => $setup ]);
                $salesSetup->addAttribute('order' , 'awd_number' ,
                    [
                        'type' => 'varchar' ,
                        'length' => 255 ,
                        'visible' => true ,
                        'required' => true ,
                        'grid' => false ,
                    ]
                )->addAttribute('order' , 'awd_status' ,
                    [
                        'type' => 'varchar' ,
                        'length' => 255 ,
                        'visible' => true ,
                        'required' => true ,
                        'grid' => false ,
                    ]
                );
            }
        }
    }

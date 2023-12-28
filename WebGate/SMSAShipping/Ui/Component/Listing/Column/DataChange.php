<?php
    
    namespace WebGate\SMSAShipping\Ui\Component\Listing\Column;
    
    use Magento\Framework\UrlInterface;
    use Magento\Framework\View\Element\UiComponent\ContextInterface;
    use Magento\Framework\View\Element\UiComponentFactory;
    use Magento\Ui\Component\Listing\Columns\Column;
    
    class DataChange extends Column
    {
        /**
         * @var UrlInterface
         */
        private $urlBuilder;
        
        public function __construct(
            ContextInterface $context ,
            UiComponentFactory $uiComponentFactory ,
            UrlInterface $urlBuilder ,
            array $components = [] ,
            array $data = []
        )
        {
            parent::__construct($context , $uiComponentFactory , $components , $data);
            $this->urlBuilder = $urlBuilder;
        }
        
        public function prepareDataSource(array $dataSource)
        {
            if(isset($dataSource['data']['items']))
            {
                foreach($dataSource['data']['items'] as &$item)
                {
                    if(isset($item['order_id']))
                    {
                        $link = $this->urlBuilder->getUrl('sales/order/view' , [
                            'order_id' => $item['order_id'],
                        ]);
                        $item['order_id'] ="<a href='{$link}'>{$item['order_id']}</a>" ;
                    }
    
                    if(isset($item['customer_id']))
                    {
                        if($item['customer_id'] === '0')
                        {
                            $item['customer_id'] = 'guest';
                        }
                        else{
                            $link = $this->urlBuilder->getUrl('customer/index/edit' , [
                                'id' => $item['customer_id'],
                            ]);
                            $item['customer_id'] ="<a href='{$link}'>{$item['customer_name']}</a>" ;
                        }
                    }
                }
            }
            
            return $dataSource;
        }
        
    }
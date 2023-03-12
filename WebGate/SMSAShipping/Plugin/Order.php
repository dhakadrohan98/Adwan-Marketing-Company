<?php
    
    namespace WebGate\SMSAShipping\Plugin;
    
    use Exception;
    use Magento\Sales\Api\OrderManagementInterface;
    use Magento\Sales\Model\Order\Address\Renderer;
    use Magento\Sales\Model\Order\Interceptor;
    use WebGate\SMSAShipping\Helper\Data;
    use WebGate\SMSAShipping\Helper\SMSA;
    use WebGate\SMSAShipping\Model\SmsashippinglogsFactory;
    
    class Order
    {
        /**
         * @var SmsashippinglogsFactory
         */
        private $smsashippinglogsFactory;
        /**
         * @var SMSA
         */
        private $SMSA;
        /**
         * @var Renderer
         */
        private $addressRenderer;
        /**
         * @var Data
         */
        private $dataHelper;
        
        public function __construct(
            SmsashippinglogsFactory $smsashippinglogsFactory ,
            SMSA $SMSA ,
            Data $dataHelper ,
            Renderer $addressRenderer
        )
        {
            
            $this->smsashippinglogsFactory = $smsashippinglogsFactory;
            $this->SMSA = $SMSA;
            $this->addressRenderer = $addressRenderer;
            $this->dataHelper = $dataHelper;
        }
        
        public function afterPlace(OrderManagementInterface $orderManagementInterface , Interceptor $order)
        {
            if($order->getShippingMethod() != 'awb_method_awb_method')
            {
                return $order;
            }
            
            $address = $order->getShippingAddress();
            $customer_name = $address->getFirstname() . ' ' . $address->getLastname();
            $full_address = $this->addressRenderer->format($address , 'text');
            
            $data = [
                'passKey' => $this->dataHelper->getPassKey() ,
                'refNo' => $order->getId() ,
                'sentDate' => date('Y-m-d H:i:s') ,
                'idNo' => $order->getId() ,
                'cName' => $customer_name ,
                'cntry' => $address->getCountryId() , // 'Riyadh'
                'cCity' => $address->getCity() ,
                'cZip' => $address->getPostcode() ,
                'cPOBox' => $address->getPostcode() ,
                'cMobile' => $address->getTelephone() ,
                'cTel1' => $address->getTelephone() ,
                'cTel2' => '' ,
                'cAddr1' => $full_address ,
                'cAddr2' => '' ,
                'shipType' => 'DLV' ,
                'PCs' => $order->getTotalItemCount() ,
                'cEmail' => $address->getEmail() ,
                'carrValue' => '' ,
                'carrCurr' => $order->getOrderCurrency()->getCurrencySymbol() ,
                'codAmt' => $order->getPayment()->getMethod() == 'cashondelivery' ? $order->getTotalDue() : '0' ,
                'weight' => $order->getWeight() ,
                'custVal' => '' ,
                'custCurr' => $order->getOrderCurrencyCode() ,
                'insrAmt' => '' ,
                'insrCurr' => '' ,
                'itemDesc' => '' ,
                'sName' => $this->dataHelper->getShipperName() ,
                'sContact' => $this->dataHelper->getShipperContact() ,
                'sAddr1' => $this->dataHelper->getShipperAddress() ,
                'sAddr2' => '' ,
                'sCity' => $this->dataHelper->getShipperCity() ,
                'sPhone' => $this->dataHelper->getShipperPhone() ,
                'sCntry' => $this->dataHelper->getShipperCountry() ,
                'prefDelvDate' => '' ,
                'gpsPoints' => '' ,
            ];
            
            $log = $this->smsashippinglogsFactory->create();
            $log_data = [
                'order_id' => $order->getId() ,
                'customer_id' => $order->getShippingAddress()->getCustomerId() ,
                'customer_name' => $customer_name ,
            ];
            
            // send data soap
            $SMSA = $this->SMSA->addShipMPS($data);
            if($SMSA instanceof Exception)
            {
                // submit log
                $log_data['response'] = $SMSA->getMessage();
                $log_data['awd_status'] = 'error';
                $log->setData($log_data)->save();
            }
            else
            {
                // set awd_number order
                if(is_numeric($SMSA->addShipMPSResult))
                {
                    // get status
                    $awd_status = $this->SMSA->getStatus($SMSA->addShipMPSResult);
                    if($awd_status instanceof Exception)
                    {
                        $log_data['awd_status'] = $awd_status->getMessage();
                    }
                    else
                    {
                        $log_data['awd_status'] = $awd_status;
                    }
                    
                    $order->addData([
                        'awd_number' => $SMSA->addShipMPSResult ,
                        'awd_status' => ($awd_status instanceof Exception) ? '' : $awd_status ,
                    ])->save();
                }
                else
                {
                    $log_data['awd_status'] = 'error';
                }
                
                // submit log
                $log_data['response'] = $SMSA->addShipMPSResult;
                $log->setData($log_data)->save();
                
                return $order;
                
            }
        }
    }

<?php
    
    namespace WebGate\SMSAShipping\Block\Adminhtml;
    
    class Logo extends \Magento\Config\Block\System\Config\Form\Field
    {
        protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
        {
            return "<img src='" . $this->getViewFileUrl('WebGate_SMSAShipping::img/smsa.jpeg') . "'/>";
        }
    }

